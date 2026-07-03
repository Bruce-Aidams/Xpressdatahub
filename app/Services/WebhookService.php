<?php

namespace App\Services;

use App\Models\AgentOrder;
use App\Models\ApiKey;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    private int $timeout = 10;

    public function sendOrderStatusWebhook(int $orderId, string $oldStatus, string $newStatus, string $webhookUrl, ?int $apiKeyId = null): array
    {
        if (empty($webhookUrl)) {
            return ['success' => false, 'message' => 'Webhook URL is empty', 'skipped' => true];
        }

        if (! filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'Invalid webhook URL format'];
        }

        $order = $this->getOrderDetails($orderId);
        if (! $order) {
            return ['success' => false, 'message' => 'Order not found', 'order_id' => $orderId];
        }

        $apiKeyData = $apiKeyId ? $this->getApiKeyDetails($apiKeyId) : null;

        $payload = [
            'event' => 'order.status.changed',
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'transaction_id' => $order['external_transaction_id'] ?? $orderId,
            'external_transaction_id' => $order['external_transaction_id'] ?? null,
            'status' => $newStatus,
            'phoneNumber' => $order['beneficiary_number'],
            'packageSize' => $order['package_size'],
            'amount' => floatval($order['amount']),
            'username' => $order['agent_username'] ?? null,
            'createdAt' => $order['created_at'],
            'type' => 'status_update',
            'order' => [
                'id' => $order['id'],
                'agent_id' => $order['agent_id'],
                'package_size' => $order['package_size'],
                'amount' => floatval($order['amount']),
                'beneficiary_number' => $order['beneficiary_number'],
                'network_type' => $order['network_type'],
                'status' => $newStatus,
                'created_at' => $order['created_at'],
                'status_updated_at' => now()->toDateTimeString(),
                'external_transaction_id' => $order['external_transaction_id'] ?? null,
                'external_reference' => $order['external_reference'] ?? null,
            ],
            'timestamp' => now()->toIso8601String(),
            'api_key' => $apiKeyData ? ['id' => $apiKeyData['id'], 'name' => $apiKeyData['name'] ?? null] : null,
        ];

        $signature = $this->generateSignature($payload, $apiKeyData);
        if ($signature) {
            $payload['signature'] = $signature;
        }

        $result = $this->sendHttpRequest($webhookUrl, $payload);

        $this->logWebhookAttempt($orderId, $webhookUrl, $payload, $result);

        return $result;
    }

    public function sendBulkOrderStatusWebhooks(array $orderIds, string $oldStatus, string $newStatus): array
    {
        $results = [];

        foreach ($orderIds as $orderId) {
            $orderInfo = $this->getOrderWebhookInfo($orderId);

            if ($orderInfo && ! empty($orderInfo['webhook_url'])) {
                $results[$orderId] = $this->sendOrderStatusWebhook(
                    $orderId,
                    $oldStatus,
                    $newStatus,
                    $orderInfo['webhook_url'],
                    $orderInfo['api_key_id']
                );
            } else {
                $results[$orderId] = [
                    'success' => false,
                    'message' => 'No webhook URL configured for this order',
                    'skipped' => true,
                ];
            }
        }

        return $results;
    }

    private function getOrderDetails(int $orderId): ?array
    {
        $order = DB::table('agent_orders')
            ->leftJoin('agents', 'agents.id', '=', 'agent_orders.agent_id')
            ->where('agent_orders.id', $orderId)
            ->select(
                'agent_orders.*',
                'agents.username as agent_username'
            )
            ->first();

        return $order ? (array) $order : null;
    }

    private function getApiKeyDetails(int $apiKeyId): ?array
    {
        $key = ApiKey::select('id', 'api_key', 'name', 'user_id')->find($apiKeyId);

        return $key ? $key->toArray() : null;
    }

    private function generateSignature(array $payload, ?array $apiKeyData): ?string
    {
        if (! $apiKeyData || empty($apiKeyData['api_key'])) {
            return null;
        }

        $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return hash_hmac('sha256', $payloadString, $apiKeyData['api_key']);
    }

    private function sendHttpRequest(string $url, array $payload): array
    {
        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

        try {
            $startTime = microtime(true);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Xpressdatahub-Webhook/1.0',
            ])
                ->timeout($this->timeout)
                ->post($url, $payload);

            $responseTime = round((microtime(true) - $startTime) * 1000);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Webhook sent successfully' : 'Webhook returned error status',
                'http_code' => $response->status(),
                'response' => $response->body(),
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'cURL error: '.$e->getMessage(),
                'http_code' => 0,
                'response_time_ms' => 0,
            ];
        }
    }

    private function logWebhookAttempt(int $orderId, string $webhookUrl, array $payload, array $result): void
    {
        try {
            WebhookLog::create([
                'order_id' => $orderId,
                'webhook_url' => $webhookUrl,
                'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'response' => json_encode($result, JSON_UNESCAPED_SLASHES),
                'http_code' => $result['http_code'] ?? null,
                'success' => $result['success'] ?? false,
                'response_time_ms' => $result['response_time_ms'] ?? null,
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }

    private function getOrderWebhookInfo(int $orderId): ?array
    {
        $info = AgentOrder::select('webhook_url', 'api_key_id')
            ->where('id', $orderId)
            ->first();

        return $info ? $info->toArray() : null;
    }
}
