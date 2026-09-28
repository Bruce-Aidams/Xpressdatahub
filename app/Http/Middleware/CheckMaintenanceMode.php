<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\PaymentConfigService;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // Never block the admin panel
        $adminPath = config('app.admin_path', 'admin');
        if ($request->is($adminPath) || $request->is($adminPath . '/*')) {
            return $next($request);
        }

        // Never block asset/API routes
        if ($request->is('api/*') || $request->is('build/*') || $request->is('assets/*')) {
            return $next($request);
        }

        $service = app(PaymentConfigService::class);
        $isMaintenance = $service->getConfig('maintenance_mode', 'false') === 'true';

        if ($isMaintenance) {
            return response()->view('errors.503', [], 503);
        }

        return $next($request);
    }
}
