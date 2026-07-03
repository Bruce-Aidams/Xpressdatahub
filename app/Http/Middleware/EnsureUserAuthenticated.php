<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('guest_mode')) {
            $guest = (object) [
                'id' => null,
                'username' => 'Guest',
                'first_name' => 'Guest',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'role' => 'guest',
                'status' => 'active',
                'balance' => 0,
            ];
            view()->share('currentUser', $guest);
            view()->share('userRole', 'guest');

            return $next($request);
        }

        if (! session('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Please log in to continue.');
        }

        $user = Agent::find(session('user_id'));

        if (! $user) {
            session()->forget(['user_id', 'username', 'role', 'user_login_time']);

            return redirect()->route('login')
                ->with('error', 'User account not found.');
        }

        if (isset($user->status) && $user->status !== 'active') {
            session()->forget(['user_id', 'username', 'role', 'user_login_time']);

            return redirect()->route('login')
                ->with('error', 'Your account has been '.$user->status.'.');
        }

        view()->share('currentUser', $user);
        view()->share('userRole', session('role'));

        return $next($request);
    }
}
