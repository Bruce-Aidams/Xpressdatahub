<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiUsageLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKeyValue = $request->header('X-API-Key')
            ?? $request->query('api_key')
            ?? $request->input('api_key');

        if (empty($apiKeyValue)) {
            return response()->json(['success' => false, 'message' => 'API key is required'], 401);
        }

        $apiKey = ApiKey::where('api_key', $apiKeyValue)->first();

        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Invalid API key'], 401);
        }

        if (!$apiKey->is_active) {
            return response()->json(['success' => false, 'message' => 'API key is inactive'], 403);
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'API key has expired'], 403);
        }

        $rateLimit = $apiKey->rate_limit ?? 60;
        $limiterKey = 'api_key_' . $apiKey->id;

        if (RateLimiter::tooManyAttempts($limiterKey, $rateLimit)) {
            $retryAfter = RateLimiter::availableIn($limiterKey);
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Try again in ' . $retryAfter . ' seconds',
            ], 429)->header('Retry-After', $retryAfter);
        }

        RateLimiter::hit($limiterKey, 60);

        $apiKey->update(['last_used_at' => now()]);

        $request->attributes->set('api_key', $apiKey);

        $response = $next($request);

        $this->logUsage($request, $apiKey, $response->getStatusCode());

        return $response;
    }

    private function logUsage(Request $request, ApiKey $apiKey, int $statusCode): void
    {
        try {
            ApiUsageLog::create([
                'api_key_id' => $apiKey->id,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'response_code' => $statusCode,
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
