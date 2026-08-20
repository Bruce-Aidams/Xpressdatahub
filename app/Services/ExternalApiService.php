<?php

namespace App\Services;

use App\Models\ApiConfig;
use App\Models\ApiLog;
use App\Models\PaymentConfig;

class ExternalApiService
{
    private ?string $network;

    private ?array $apiConfig;

    public function __construct(?string $network = null)
    {
        $this->network = $network;
        $this->loadApiConfig();
    }

    private function loadApiConfig(): void
    {
        if (! $this->network) {
            $this->apiConfig = null;

            return;
        }

        $config = ApiConfig::where('network_type', $this->network)
            ->where('is_active', true)
            ->first();

        $this->apiConfig = $config ? $config->toArray() : null;
    }

    public function getApiEndpoint(): string
    {
        return $this->apiConfig['endpoint_url'] ?? 'Unknown';
    }

    public function purchaseData(string $msisdn, string $network, int $capacityMb, string $paymentMethod = 'wallet', ?string $localOrderId = null): array
    {
        if (! $this->apiConfig || $this->network !== $network) {
            $this->network = $network;
            $this->loadApiConfig();
        }

        if (! $this->isGlobalApiEnabled()) {
            return ['success' => false, 'error' => 'Global API integration is disabled', 'data' => null];
        }

        if (! $this->apiConfig) {
            return ['success' => false, 'error' => "No active API configuration found for network: {$network}", 'data' => null];
        }

        $endpoint = $this->apiConfig['endpoint_url'] ?? '';
        if (empty($endpoint)) {
            return ['success' => false, 'error' => "API endpoint is not configured for network: {$network}", 'data' => null];
        }

        if (! filter_var($endpoint, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => "Invalid API endpoint URL: {$endpoint}", 'data' => null];
        }

        $requestData = $this->prepareRequestData($msisdn, $network, $capacityMb, $paymentMethod, $localOrderId);

        $startTime = microtime(true);
        $response = $this->makeApiCall($requestData);
        $executionTime = round((microtime(true) - $startTime) * 1000);

        $this->logApiCall($localOrderId, $requestData, $response, $executionTime);

        return $response;
    }

    public function checkTransactionStatus(string $externalTransactionId): array
    {
        if (! $this->isGlobalApiEnabled()) {
            return ['success' => false, 'error' => 'Global API integration is disabled', 'data' => null];
        }

        if (! $this->apiConfig) {
            return ['success' => false, 'error' => "No active API configuration found for network: {$this->network}", 'data' => null];
        }

        $requestData = [
            'transaction_id' => $externalTransactionId,
            'api_key' => $this->apiConfig['api_key'] ?? '',
            'api_secret' => $this->apiConfig['api_secret'] ?? '',
        ];

        $requestData = array_map(function ($value) use ($externalTransactionId) {
            if (is_string($value)) {
                $value = str_replace('{transaction_id}', $externalTransactionId, $value);
                $value = str_replace('{api_key}', $this->apiConfig['api_key'] ?? '', $value);
                $value = str_replace('{api_secret}', $this->apiConfig['api_secret'] ?? '', $value);
            }

            return $value;
        }, $requestData);

        $startTime = microtime(true);
        $response = $this->makeStatusCheckCall($requestData);
        $executionTime = round((microtime(true) - $startTime) * 1000);

        $this->logApiCall(null, $requestData, $response, $executionTime);

        return $response;
    }

    public function getTransactionStatus(string $externalTransactionId): array
    {
        return $this->checkTransactionStatus($externalTransactionId);
    }

    public function convertPackageSize(string $bundleLabel): int
    {
        $bundleLabel = trim($bundleLabel);

        if (preg_match('/(\d+(?:\.\d+)?)\s*GB/i', $bundleLabel, $matches)) {
            return (int) round(floatval($matches[1]) * 1024);
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*MB/i', $bundleLabel, $matches)) {
            return (int) round(floatval($matches[1]));
        }

        if (preg_match('/(\d+(?:\.\d+)?)/', $bundleLabel, $matches)) {
            $number = floatval($matches[1]);

            return $number < 100 ? (int) round($number * 1024) : (int) round($number);
        }

        return 0;
    }

    public function validatePhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        return strlen($digits) >= 9 ? $digits : null;
    }

    private function isGlobalApiEnabled(): bool
    {
        $value = PaymentConfig::where('config_key', 'api_enabled')->value('config_value');

        // If no row exists yet, default to enabled
        if ($value === null) {
            return true;
        }

        return (bool) $value;
    }

    private function prepareRequestData(string $msisdn, string $network, int $capacityMb, string $paymentMethod, ?string $localOrderId): array
    {
        $template = $this->apiConfig['request_body_template'] ?? '{}';
        $capacityGb = round($capacityMb / 1024, 2);
        
        $amount = $capacityMb; // fallback
        $reference = $localOrderId ?: ('ORDER-'.time().'-'.rand(100, 999));
        $orderId = $localOrderId;
        $packageVal = $capacityMb.'MB';

        if ($localOrderId) {
            $order = \App\Models\Order::find($localOrderId);
            if ($order) {
                $amount = $order->amount;
                $reference = $order->order_reference ?: $order->transaction_id ?: $localOrderId;
                $orderId = $order->id;
                $packageVal = $order->package_size ?: ($capacityMb.'MB');
                $msisdn = $order->phone_number ?: $msisdn;
            }
        }

        $data = str_replace(
            ['{phone}', '{package}', '{amount}', '{network}', '{payment_method}', '{order_id}', '{capacity}', '{mb}', '{volume}', '{reference}', '{referrer}', '{webhook}'],
            [$msisdn, $packageVal, $amount, $network, $paymentMethod, $orderId, $capacityGb, $capacityMb, $capacityMb, $reference, $msisdn, url('/api/v1/webhook/status-update')],
            $template
        );

        return json_decode($data, true) ?? [];
    }

    private function makeApiCall(array $requestData): array
    {
        $endpoint = $this->apiConfig['endpoint_url'];
        $method = $this->apiConfig['request_method'] ?? 'POST';
        $headers = json_decode($this->apiConfig['request_headers'] ?? '{}', true) ?: [];
        $timeout = intval($this->apiConfig['timeout_seconds'] ?? 30);

        if ($timeout <= 0 || $timeout > 300) {
            $timeout = 30;
        }

        $connectTimeout = min(10, max(5, intval($timeout / 3)));

        foreach ($headers as $key => $value) {
            $headers[$key] = str_replace('{api_key}', $this->apiConfig['api_key'] ?? '', $value ?? '');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_VERBOSE => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        $curlInfo = [
            'total_time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
            'connect_time' => curl_getinfo($ch, CURLINFO_CONNECT_TIME),
            'namelookup_time' => curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME),
            'starttransfer_time' => curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
            'primary_ip' => curl_getinfo($ch, CURLINFO_PRIMARY_IP),
            'primary_port' => curl_getinfo($ch, CURLINFO_PRIMARY_PORT),
            'size_upload' => curl_getinfo($ch, CURLINFO_SIZE_UPLOAD),
            'size_download' => curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
        ];

        curl_close($ch);

        if ($error) {
            error_log("API cURL Error - Network: {$this->network}, Endpoint: {$endpoint}, Error: {$error}, Timeout: {$timeout}s");

            return [
                'success' => false,
                'error' => "cURL Error: {$error}",
                'error_type' => 'curl_error',
                'data' => null,
                'http_code' => 0,
                'raw_response' => null,
                'curl_info' => $curlInfo,
            ];
        }

        $responseData = json_decode($response, true);

        $successField = $this->apiConfig['response_success_field'] ?? 'success';
        $isSuccess = isset($responseData[$successField])
            ? (bool) $responseData[$successField]
            : ($httpCode >= 200 && $httpCode < 300);

        $errorMessage = null;
        $errorType = 'unknown';

        if (! $isSuccess) {
            if ($httpCode >= 400 && $httpCode < 500) {
                $errorType = 'client_error';
                $errorMessage = "HTTP {$httpCode}: ".($responseData['reason'] ?? $responseData['message'] ?? $responseData['error'] ?? 'Client error');
            } elseif ($httpCode >= 500) {
                $errorType = 'server_error';
                $errorMessage = "HTTP {$httpCode}: ".($responseData['reason'] ?? $responseData['message'] ?? $responseData['error'] ?? 'Server error');
            } elseif (isset($responseData['reason'])) {
                $errorType = 'api_error';
                $errorMessage = $responseData['reason'];
            } elseif (isset($responseData['message'])) {
                $errorType = 'api_message';
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['error'])) {
                $errorType = 'api_error';
                $errorMessage = $responseData['error'];
            } else {
                $errorType = 'unknown_error';
                $errorMessage = "API call failed - HTTP {$httpCode}";
            }

            error_log("API Error - Network: {$this->network}, HTTP: {$httpCode}, Type: {$errorType}, Message: {$errorMessage}");
        }

        return [
            'success' => $isSuccess,
            'data' => $responseData,
            'http_code' => $httpCode,
            'raw_response' => $response,
            'error' => $errorMessage,
            'error_type' => $errorType,
        ];
    }

    private function makeStatusCheckCall(array $requestData): array
    {
        $endpoint = $this->apiConfig['status_endpoint'] ?? $this->apiConfig['endpoint_url'];
        $method = $this->apiConfig['request_method'] ?? 'POST';
        $headers = json_decode($this->apiConfig['request_headers'] ?? '{}', true) ?: [];

        foreach ($headers as $key => $value) {
            $headers[$key] = str_replace('{api_key}', $this->apiConfig['api_key'] ?? '', $value ?? '');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Connection failed: '.$curlError,
                'data' => null,
                'http_code' => $httpCode,
                'raw_response' => null,
            ];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $data,
                'error' => null,
                'http_code' => $httpCode,
                'raw_response' => $response,
            ];
        } else {
            return [
                'success' => false,
                'error' => $data['message'] ?? $data['error'] ?? 'API request failed',
                'data' => $data,
                'http_code' => $httpCode,
                'raw_response' => $response,
            ];
        }
    }

    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }

        return $formatted;
    }

    private function logApiCall(?string $localOrderId, array $requestData, array $response, int $executionTime): void
    {
        try {
            ApiLog::create([
                'endpoint' => $this->apiConfig['endpoint_url'] ?? '',
                'request_data' => json_encode($requestData),
                'response_data' => json_encode(array_merge(
                    $response['data'] ?? [],
                    ['_meta' => [
                        'http_code' => $response['http_code'] ?? 0,
                        'success' => $response['success'] ?? false,
                        'error' => $response['error'] ?? null,
                        'error_type' => $response['error_type'] ?? null,
                        'execution_time_ms' => $executionTime,
                        'network' => $this->network,
                        'order_id' => $localOrderId,
                    ]]
                )),
                'status_code' => $response['http_code'] ?? 0,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
