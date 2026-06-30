<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\LowBalanceAlert;
use App\Models\PaymentConfig;
use Illuminate\Support\Facades\DB;

class LowBalanceAlertService
{
    public function getConfig(): array
    {
        return [
            'enabled' => (bool) PaymentConfig::where('config_key', 'low_balance_alert_enabled')->value('config_value'),
            'threshold_amount' => floatval(PaymentConfig::where('config_key', 'low_balance_threshold')->value('config_value') ?? 50),
            'alert_interval_days' => intval(PaymentConfig::where('config_key', 'low_balance_alert_interval')->value('config_value') ?? 2),
        ];
    }

    public function updateConfig(array $data): bool
    {
        $configs = [
            'low_balance_alert_enabled' => ($data['enabled'] ?? false) ? '1' : '0',
            'low_balance_threshold' => (string) ($data['threshold_amount'] ?? 50),
            'low_balance_alert_interval' => (string) ($data['alert_interval_days'] ?? 2),
        ];

        foreach ($configs as $key => $value) {
            PaymentConfig::updateOrCreate(
                ['config_key' => $key],
                ['config_value' => $value]
            );
        }

        return true;
    }

    public function getUsersNeedingAlerts(): array
    {
        $config = $this->getConfig();

        if (!$config['enabled']) {
            return [];
        }

        $threshold = $config['threshold_amount'];
        $intervalDays = $config['alert_interval_days'];
        $cutoffDate = now()->subDays($intervalDays);

        $lowBalanceUsers = Agent::whereRaw('COALESCE(balance, 0) < ?', [$threshold])
            ->get();

        $users = [];
        foreach ($lowBalanceUsers as $user) {
            $lastAlert = LowBalanceAlert::where('user_id', $user->id)
                ->max('alert_sent_at');

            if (!$lastAlert || $lastAlert < $cutoffDate) {
                $users[] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'balance' => floatval($user->balance ?? 0),
                    'role' => $user->role,
                    'phone' => $user->phone,
                ];
            }
        }

        return $users;
    }

    public function processAlerts(): array
    {
        $config = $this->getConfig();

        if (!$config['enabled']) {
            return ['total' => 0, 'sent' => 0, 'failed' => 0];
        }

        $users = $this->getUsersNeedingAlerts();
        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            if (empty($user['phone'])) {
                $failed++;
                continue;
            }

            $result = $this->sendAlert(
                $user['id'],
                $user['username'],
                $user['balance'],
                $config['threshold_amount'],
                $user['phone']
            );

            if ($result) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'total' => count($users),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    public function recordAlertSent(int $userId): void
    {
        LowBalanceAlert::create([
            'user_id' => $userId,
            'alert_sent_at' => now(),
        ]);
    }

    private function sendAlert(int $userId, string $username, float $balance, float $threshold, string $phone): bool
    {
        $balanceFormatted = number_format($balance, 2);
        $thresholdFormatted = number_format($threshold, 2);
        $message = "Low Balance Alert - Xpressdatahub\n\n";
        $message .= "Hello {$username},\n\n";
        $message .= "Your account balance is running low!\n\n";
        $message .= "Current Balance: GH₵ {$balanceFormatted}\n";
        $message .= "Threshold: GH₵ {$thresholdFormatted}\n\n";
        $message .= "Please top up your account to continue enjoying our services.\n\n";
        $message .= "Thank you,\nXpressdatahub";

        $smsResult = $this->sendSMS($phone, $message);

        if ($smsResult) {
            $this->recordAlertSent($userId);
        }

        return $smsResult;
    }

    private function sendSMS(string $phoneNumber, string $message): bool
    {
        $smsConfig = json_decode(
            DB::table('notification_config')->where('type', 'sms')->value('config_data') ?? '{}',
            true
        );

        if (empty($smsConfig['enabled'])) {
            return false;
        }

        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if (strpos($phoneNumber, '0') === 0) {
            $phoneNumber = '233' . substr($phoneNumber, 1);
        }

        $provider = $smsConfig['provider'] ?? 'generic';

        try {
            if ($provider === 'smsonlinegh') {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Host' => 'api.smsonlinegh.com',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'key ' . ($smsConfig['api_key'] ?? ''),
                ])->timeout(30)->post('https://api.smsonlinegh.com/v5/sms/send', [
                    'text' => $message,
                    'type' => 0,
                    'sender' => $smsConfig['sender_id'] ?? '',
                    'destinations' => [$phoneNumber],
                ]);
                return $response->successful();
            }

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post($smsConfig['api_url'] ?? '', [
                    'phone' => $phoneNumber,
                    'message' => $message,
                ]);
            return $response->successful();
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
