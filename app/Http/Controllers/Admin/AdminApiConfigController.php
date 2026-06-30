<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiConfig;
use Illuminate\Http\Request;

class AdminApiConfigController extends Controller
{
    public function index()
    {
        $configs = ApiConfig::orderByDesc('is_active')->orderByDesc('updated_at')->get();

        return view('admin.config.api-config', compact('configs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'network_type' => 'required|string|max:50',
            'api_name' => 'required|string|max:255',
            'api_endpoint' => 'required|url|max:500',
            'status_endpoint' => 'nullable|url|max:500',
            'api_key' => 'required|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'request_method' => 'nullable|string|in:GET,POST,PUT,PATCH',
            'request_headers' => 'nullable|string',
            'request_body_template' => 'nullable|string',
            'response_success_field' => 'nullable|string|max:100',
            'response_data_field' => 'nullable|string|max:100',
            'response_error_field' => 'nullable|string|max:100',
            'timeout_seconds' => 'nullable|integer|min:5|max:300',
            'retry_attempts' => 'nullable|integer|min:0|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $this->validateJson($request, 'request_headers');
            $this->validateJson($request, 'request_body_template');

            $existing = ApiConfig::where('network_type', $request->input('network_type'))->first();

            $data = [
                'network_type' => $request->input('network_type'),
                'api_name' => $request->input('api_name'),
                'endpoint_url' => $request->input('api_endpoint'),
                'status_endpoint' => $request->input('status_endpoint'),
                'api_key' => $request->input('api_key'),
                'api_secret' => $request->input('api_secret'),
                'request_method' => $request->input('request_method', 'POST'),
                'request_headers' => $request->input('request_headers'),
                'request_body_template' => $request->input('request_body_template'),
                'response_success_field' => $request->input('response_success_field', 'success'),
                'response_data_field' => $request->input('response_data_field', 'data'),
                'response_error_field' => $request->input('response_error_field', 'error'),
                'is_active' => $request->boolean('is_active', true),
                'timeout_seconds' => $request->input('timeout_seconds', 30),
                'retry_attempts' => $request->input('retry_attempts', 3),
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                ApiConfig::create($data);
            }

            return redirect()->back()->with('success', 'API configuration saved successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save API configuration.');
        }
    }

    public function toggle(ApiConfig $apiConfig)
    {
        try {
            $apiConfig->update([
                'is_active' => !$apiConfig->is_active,
            ]);

            $status = $apiConfig->is_active ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "{$apiConfig->network_type} API {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update API status.');
        }
    }

    public function destroy(ApiConfig $apiConfig)
    {
        try {
            $apiConfig->delete();
            return redirect()->back()->with('success', 'API configuration deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete configuration.');
        }
    }

    public function testConnection(ApiConfig $apiConfig)
    {
        $endpoint = $apiConfig->endpoint_url;
        $method = $apiConfig->request_method ?? 'POST';
        $timeout = max(5, min(30, intval($apiConfig->timeout_seconds ?? 10)));
        $headers = json_decode($apiConfig->request_headers ?? '{}', true) ?: [];

        foreach ($headers as $key => $value) {
            $headers[$key] = str_replace('{api_key}', $apiConfig->api_key ?? '', $value ?? '');
        }

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        $testData = [
            'phone' => '233500000000',
            'network' => $apiConfig->network_type,
            'package' => '1024MB',
            'amount' => 1,
            'payment_method' => 'wallet',
            'order_id' => 'TEST-' . time(),
            'reference' => 'TEST-' . time(),
        ];

        $startTime = microtime(true);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => min(10, max(3, intval($timeout / 3))),
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => array_map(fn($k, $v) => "{$k}: {$v}", array_keys($headers), array_values($headers)),
                CURLOPT_POSTFIELDS => $method !== 'GET' ? json_encode($testData) : null,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $totalTime = round((microtime(true) - $startTime) * 1000);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            $curlInfo = [
                'total_time' => round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000, 2),
                'connect_time' => round(curl_getinfo($ch, CURLINFO_CONNECT_TIME) * 1000, 2),
                'namelookup_time' => round(curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME) * 1000, 2),
                'starttransfer_time' => round(curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME) * 1000, 2),
                'primary_ip' => curl_getinfo($ch, CURLINFO_PRIMARY_IP),
                'primary_port' => curl_getinfo($ch, CURLINFO_PRIMARY_PORT),
                'size_download' => curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
            ];
            curl_close($ch);

            if ($curlError) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => "cURL Error ({$curlErrno}): {$curlError}",
                    'details' => [
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'timeout' => $timeout . 's',
                        'http_code' => 0,
                        'response_time' => $totalTime . 'ms',
                        'curl_info' => $curlInfo,
                    ],
                ]);
            }

            $responseData = json_decode($response, true);
            $successField = $apiConfig->response_success_field ?? 'success';
            $isSuccess = isset($responseData[$successField])
                ? (bool) $responseData[$successField]
                : ($httpCode >= 200 && $httpCode < 300);

            return response()->json([
                'success' => $isSuccess,
                'status' => $isSuccess ? 'success' : 'failed',
                'message' => $isSuccess
                    ? "Connection successful — HTTP {$httpCode}"
                    : "Connection failed — HTTP {$httpCode}",
                'details' => [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'timeout' => $timeout . 's',
                    'http_code' => $httpCode,
                    'response_time' => $totalTime . 'ms',
                    'curl_info' => $curlInfo,
                    'response' => $responseData ? json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $response,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
                'details' => [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'timeout' => $timeout . 's',
                ],
            ]);
        }
    }

    private function validateJson(Request $request, string $field): void
    {
        $value = $request->input($field);
        if ($value && $value !== '') {
            json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], []),
                    new \Exception(ucfirst(str_replace('_', ' ', $field)) . ' must be valid JSON.')
                );
            }
        }
    }
}
