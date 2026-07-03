<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\NotificationConfig;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Http;
use PHPMailer\PHPMailer\PHPMailer;

class NotificationService
{
    private array $smsConfig;

    private array $emailConfig;

    public function __construct()
    {
        $this->loadConfigurations();
    }

    private function loadConfigurations(): void
    {
        $smsRecord = NotificationConfig::where('type', 'sms')->first();
        $this->smsConfig = $smsRecord ? json_decode($smsRecord->config_data, true) ?? [] : [];

        $emailRecord = NotificationConfig::where('type', 'email')->first();
        $this->emailConfig = $emailRecord ? json_decode($emailRecord->config_data, true) ?? [] : [];
    }

    public function sendNotification(int $userId, string $type, array $data = []): array
    {
        $results = ['sms' => false, 'email' => false, 'errors' => []];

        $user = Agent::select('id', 'username', 'email', 'phone')->find($userId);
        if (! $user) {
            $results['errors'][] = 'User not found';

            return $results;
        }

        $template = NotificationTemplate::where('type', $type)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            $results['errors'][] = 'Notification template not found';

            return $results;
        }

        $processedTemplate = $this->processTemplate($template, $data, $user);

        if (! empty($this->smsConfig['enabled']) && ! empty($user->phone)) {
            $results['sms'] = $this->sendSMS($user->phone, $processedTemplate['sms']);
        }

        if (! empty($this->emailConfig['enabled']) && ! empty($user->email)) {
            $results['email'] = $this->sendEmail(
                $user->email,
                $processedTemplate['email']['subject'],
                $processedTemplate['email']['body']
            );
        }

        $this->logNotification($userId, $type, $results, $data);

        return $results;
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        if (empty($this->emailConfig['enabled'])) {
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->emailConfig['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->emailConfig['smtp_username'];
            $mail->Password = $this->emailConfig['smtp_password'];
            $mail->Port = $this->emailConfig['smtp_port'] ?? 587;
            $mail->SMTPSecure = $mail->Port == 465
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name'] ?? 'Xpressdatahub');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    public function updateConfiguration(string $key, array $config): bool
    {
        NotificationConfig::updateOrCreate(
            ['type' => $key],
            ['config_data' => json_encode($config), 'updated_at' => now()]
        );

        $this->loadConfigurations();

        return true;
    }

    public function getNotificationLogs(array $filters = []): array
    {
        $query = NotificationLog::with('agent:id,username,email');

        if (! empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        if (! empty($filters['offset'])) {
            $query->offset($filters['offset']);
        }

        return $query->orderByDesc('created_at')->get()->toArray();
    }

    private function processTemplate(NotificationTemplate $template, array $data, Agent $user): array
    {
        $processed = [
            'sms' => $template->sms_template,
            'email' => [
                'subject' => $template->email_subject,
                'body' => $template->email_template,
            ],
        ];

        $placeholders = [
            '{username}' => $user->username,
            '{email}' => $user->email,
            '{phone}' => $user->phone,
            '{amount}' => $data['amount'] ?? '',
            '{balance}' => $data['balance'] ?? '',
            '{date}' => $data['date'] ?? now()->toDateTimeString(),
            '{ip_address}' => $data['ip_address'] ?? 'Unknown',
            '{user_agent}' => $data['user_agent'] ?? 'Unknown',
            '{site_name}' => 'Xpressdatahub',
        ];

        foreach ($placeholders as $placeholder => $value) {
            $processed['sms'] = str_replace($placeholder, $value, $processed['sms']);
            $processed['email']['subject'] = str_replace($placeholder, $value, $processed['email']['subject']);
            $processed['email']['body'] = str_replace($placeholder, $value, $processed['email']['body']);
        }

        return $processed;
    }

    private function sendSMS(string $phoneNumber, string $message): bool
    {
        if (empty($this->smsConfig['enabled'])) {
            return false;
        }

        $phoneNumber = $this->cleanPhoneNumber($phoneNumber);
        if (! $phoneNumber) {
            return false;
        }

        $provider = $this->smsConfig['provider'] ?? 'generic';

        if ($provider === 'smsonlinegh') {
            return $this->sendSMSViaSMSOnlineGH($phoneNumber, $message);
        }

        return $this->sendSMSViaGeneric($phoneNumber, $message);
    }

    private function sendSMSViaGeneric(string $phoneNumber, string $message): bool
    {
        $url = $this->smsConfig['api_url'] ?? '';
        $apiKey = $this->smsConfig['api_key'] ?? '';
        $senderId = $this->smsConfig['sender_id'] ?? '';

        if (empty($url) || empty($apiKey)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($url, [
                'to' => $phoneNumber,
                'message' => $message,
                'sender_id' => $senderId,
                'api_key' => $apiKey,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    private function sendSMSViaSMSOnlineGH(string $phoneNumber, string $message): bool
    {
        $apiKey = $this->smsConfig['api_key'] ?? '';
        $senderId = $this->smsConfig['sender_id'] ?? '';

        if (empty($apiKey) || empty($senderId)) {
            return false;
        }

        if (strpos($phoneNumber, '233') !== 0) {
            $phoneNumber = '233'.ltrim($phoneNumber, '0');
        }

        try {
            $response = Http::withHeaders([
                'Host' => 'api.smsonlinegh.com',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'key '.$apiKey,
            ])->timeout(30)->post('https://api.smsonlinegh.com/v5/sms/send', [
                'text' => $message,
                'type' => 0,
                'sender' => $senderId,
                'destinations' => [$phoneNumber],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    private function cleanPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) == 10 && $phone[0] === '0') {
            $phone = '233'.substr($phone, 1);
        } elseif (strlen($phone) == 9) {
            $phone = '233'.$phone;
        }

        return $phone;
    }

    private function logNotification(int $userId, string $type, array $results, array $data): void
    {
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'notification_type' => $type,
                'sms_sent' => $results['sms'] ? 1 : 0,
                'email_sent' => $results['email'] ? 1 : 0,
                'errors' => json_encode($results['errors']),
                'data' => json_encode($data),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
