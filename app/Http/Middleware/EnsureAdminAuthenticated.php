<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('admin_logged_in') || !session('admin_id')) {
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        $admin = AdminUser::find(session('admin_id'));

        if (!$admin || !$admin->is_active) {
            session()->forget(['admin_logged_in', 'admin_id', 'admin_username', 'admin_role', 'login_time']);
            return redirect()->route('admin.login')
                ->with('error', 'Your account has been deactivated.');
        }

        $loginTime = session('login_time');
        if ($loginTime && (now()->timestamp - $loginTime) > 7200) {
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please log in again.');
        }

        view()->share('adminUser', $admin);
        view()->share('adminRole', session('admin_role'));

        return $next($request);
    }
}
