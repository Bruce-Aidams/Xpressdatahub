<?php

namespace App\Console\Commands;

use App\Models\ApiConfig;
use App\Models\PaymentConfig;
use Illuminate\Console\Command;

class TestApiConnection extends Command
{
    protected $signature = 'api:test-connection {network? : Network type to test (MTN, Telecel, AirtelTigo)}';

    protected $description = 'Test connectivity to external data API endpoints';

    public function handle(): int
    {
        $networkFilter = $this->argument('network');

        $globalEnabled = PaymentConfig::where('config_key', 'api_enabled')->value('config_value');
        if (!$globalEnabled) {
            $this->error('Global API integration is disabled. Enable it in Payment Config (api_enabled).');
            return static::FAILURE;
        }

        $query = ApiConfig::where('is_active', true);
        if ($networkFilter) {
            $query->where('network_type', $networkFilter);
        }
        $configs = $query->get();

        if ($configs->isEmpty()) {
            $this->error('No active API configurations found' . ($networkFilter ? " for network: {$networkFilter}" : '') . '.');
            return static::FAILURE;
        }

        $this->info('Testing external API connections...');
        $this->newLine();

        $headers = ['Network', 'Endpoint', 'Status', 'HTTP Code', 'Response Time', 'Message'];
        $rows = [];

        foreach ($configs as $config) {
            $endpoint = $config->status_endpoint ?: $config->endpoint_url;

            if (empty($endpoint)) {
                $rows[] = [$config->network_type, '(not set)', 'SKIP', '-', '-', 'No endpoint configured'];
                continue;
            }

            if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
                $rows[] = [$config->network_type, $this->truncate($endpoint, 40), 'FAIL', '-', '-', 'Invalid URL'];
                continue;
            }

            $result = $this->testEndpoint($config, $endpoint);

            $statusIcon = $result['success'] ? 'OK' : 'FAIL';
            $rows[] = [
                $config->network_type,
                $this->truncate($endpoint, 40),
                $statusIcon,
                $result['http_code'] ?? '-',
                ($result['response_time_ms'] ?? 0) . 'ms',
                $this->truncate($result['message'] ?? '', 50),
            ];
        }

        $this->table($headers, $rows);

        $okCount = count(array_filter($rows, fn($r) => $r[2] === 'OK'));
        $failCount = count(array_filter($rows, fn($r) => $r[2] === 'FAIL'));
        $skipCount = count(array_filter($rows, fn($r) => $r[2] === 'SKIP'));

        $this->newLine();
        $this->info("Results: {$okCount} connected, {$failCount} failed, {$skipCount} skipped");

        return $failCount > 0 ? static::FAILURE : static::SUCCESS;
    }

    private function testEndpoint(ApiConfig $config, string $endpoint): array
    {
        $method = $config->request_method ?? 'POST';
        $headers = json_decode($config->request_headers ?? '{}', true) ?: [];
        $timeout = min(15, max(5, intval($config->timeout_seconds ?? 30)));

        foreach ($headers as $key => $value) {
            $headers[$key] = str_replace('{api_key}', $config->api_key ?? '', $value ?? '');
        }

        $testPayload = [
            'test' => true,
            'transaction_id' => 'TEST-' . time(),
            'api_key' => $config->api_key ?? '',
            'api_secret' => $config->api_secret ?? '',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_POSTFIELDS => json_encode($testPayload),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $responseTime = round((microtime(true) - $startTime) * 1000);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'http_code' => 0,
                'response_time_ms' => $responseTime,
                'message' => "cURL Error: {$error}",
            ];
        }

        $isSuccess = $httpCode >= 200 && $httpCode < 500;
        $message = $isSuccess ? 'Connection successful' : "HTTP {$httpCode}";

        $data = json_decode($response, true);
        if ($data && isset($data['message'])) {
            $message = $data['message'];
        } elseif ($data && isset($data['error'])) {
            $message = $data['error'];
        }

        return [
            'success' => $isSuccess,
            'http_code' => $httpCode,
            'response_time_ms' => $responseTime,
            'message' => $message,
        ];
    }

    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }
        return $formatted;
    }

    private function truncate(string $value, int $length): string
    {
        return strlen($value) > $length ? substr($value, 0, $length - 3) . '...' : $value;
    }
}
