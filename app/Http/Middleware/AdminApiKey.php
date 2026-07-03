<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdminApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Admin authentication required. Provide a Bearer token.',
            ], 401);
        }

        $admin = AdminUser::where('api_token', $token)->first();

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin token.',
            ], 401);
        }

        if (! $admin->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Admin account is deactivated.',
            ], 403);
        }

        $limiterKey = 'admin_api_'.$admin->id;
        if (RateLimiter::tooManyAttempts($limiterKey, 120)) {
            $retryAfter = RateLimiter::availableIn($limiterKey);

            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Try again in '.$retryAfter.' seconds.',
            ], 429)->header('Retry-After', $retryAfter);
        }

        RateLimiter::hit($limiterKey, 60);

        $request->attributes->set('admin_user', $admin);

        return $next($request);
    }
}
