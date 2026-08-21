<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiConfig;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminApiConfigController extends Controller
{
    public function index()
    {
        $configs = ApiConfig::orderByDesc('is_active')->orderByDesc('updated_at')->get();
        $isLocked = config('api-connections.locked', false);
        $maxConnections = config('api-connections.max_connections', 3);
        $canAddMore = $configs->count() < $maxConnections;

        return view('admin.config.api-config', compact('configs', 'isLocked', 'canAddMore'));
    }

    public function store(Request $request)
    {
        if (config('api-connections.locked', false)) {
            return redirect()->back()->with('error', 'API configurations are currently locked and cannot be modified.');
        }

        $request->validate([
            'network_type' => 'required|string|max:50',
            'api_name' => 'required|string|max:255',
            'api_endpoint' => 'required|url|max:500',
            'status_endpoint' => 'nullable|string|max:500',
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

            if (! $existing && ApiConfig::count() >= config('api-connections.max_connections', 3)) {
                return redirect()->back()->with('error', 'Maximum API connections limit reached.');
            }

            $data = [
                'network_type' => $request->input('network_type'),
                'api_name' => $request->input('api_name'),
                'endpoint_url' => $request->input('api_endpoint'),
                'status_endpoint' => $request->input('status_endpoint') ?: null,
                'api_key' => $request->input('api_key'),
                'api_secret' => $request->input('api_secret') ?: null,
                'request_method' => $request->input('request_method', 'POST'),
                'request_headers' => $request->input('request_headers') ?: null,
                'request_body_template' => $request->input('request_body_template') ?: null,
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

        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (! empty($errors)) {
                $first = collect($errors)->map(fn ($msgs) => $msgs[0])->implode(', ');
            } else {
                $first = $e->getMessage();
            }

            return redirect()->back()->with('error', 'Validation failed: '.$first)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save API configuration. '.$e->getMessage())->withInput();
        }
    }

    public function toggle(ApiConfig $apiConfig)
    {
        if (config('api-connections.locked', false)) {
            return redirect()->route('admin.api-config')->with('error', 'API configurations are currently locked.');
        }

        try {
            $apiConfig->update([
                'is_active' => ! $apiConfig->is_active,
            ]);

            $status = $apiConfig->is_active ? 'activated' : 'deactivated';

            return redirect()->route('admin.api-config')->with('success', "{$apiConfig->network_type} API {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.api-config')->with('error', 'Failed to update API status.');
        }
    }

    public function destroy(ApiConfig $apiConfig)
    {
        if (config('api-connections.locked', false)) {
            return redirect()->route('admin.api-config')->with('error', 'API configurations are currently locked.');
        }

        try {
            $apiConfig->delete();

            return redirect()->route('admin.api-config')->with('success', 'API configuration deleted.');
        } catch (\Exception $e) {
            return redirect()->route('admin.api-config')->with('error', 'Failed to delete configuration.');
        }
    }

    public function testConnection(ApiConfig $apiConfig)
    {
        $endpoint = $apiConfig->endpoint_url;
        $timeout = max(5, min(30, intval($apiConfig->timeout_seconds ?? 10)));
        $headers = json_decode($apiConfig->request_headers ?? '{}', true) ?: [];

        foreach ($headers as $key => $value) {
            $headers[$key] = str_replace('{api_key}', $apiConfig->api_key ?? '', $value ?? '');
        }

        if (! isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        $startTime = microtime(true);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => min(10, max(3, intval($timeout / 3))),
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array_map(fn ($k, $v) => "{$k}: {$v}", array_keys($headers), array_values($headers)),
                CURLOPT_POSTFIELDS => null,
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
                        'method' => 'GET',
                        'timeout' => $timeout.'s',
                        'http_code' => 0,
                        'response_time' => $totalTime.'ms',
                        'curl_info' => $curlInfo,
                    ],
                ]);
            }

            $isSuccess = ($httpCode > 0);

            return response()->json([
                'success' => $isSuccess,
                'status' => $isSuccess ? 'success' : 'failed',
                'message' => $isSuccess
                    ? "Connection test successful — Host is reachable (HTTP {$httpCode})"
                    : "Connection failed — HTTP {$httpCode}",
                'details' => [
                    'endpoint' => $endpoint,
                    'method' => 'GET',
                    'timeout' => $timeout.'s',
                    'http_code' => $httpCode,
                    'response_time' => $totalTime.'ms',
                    'curl_info' => $curlInfo,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Exception: '.$e->getMessage(),
                'details' => [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'timeout' => $timeout.'s',
                ],
            ]);
        }
    }

    private function validateJson(Request $request, string $field): void
    {
        $value = $request->input($field);
        if ($value && is_string($value) && $value !== '') {
            json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $label = ucfirst(str_replace('_', ' ', $field));
                throw new \Exception("{$label} must be valid JSON. Error: ".json_last_error_msg());
            }
        }
    }
}
