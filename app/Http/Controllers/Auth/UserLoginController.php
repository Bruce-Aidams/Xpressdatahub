<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\UserLoginTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserLoginController extends Controller
{
    public function __construct(
        private UserLoginTracker $loginTracker
    ) {}

    public function showLoginForm()
    {
        if (session('user_id') || session('guest_mode')) {
            return redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

    public function guestLogin()
    {
        session()->regenerate();
        session()->put('guest_mode', true);
        session()->put('guest临时_id', 'guest-' . Str::random(12));
        session()->put('guest_id', 'GST-' . strtoupper(Str::random(6)));
        session()->put('user_login_time', now()->timestamp);

        return redirect()->route('user.dashboard')
            ->with('success', 'Welcome! You are browsing as a guest. Payment will be via Paystack.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $agent = Agent::where('username', $request->input('username'))
            ->orWhere('email', $request->input('username'))
            ->first();

        if (!$agent || !Hash::check($request->input('password'), $agent->password_hash)) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', 'Invalid credentials.');
        }

        if (isset($agent->status) && $agent->status !== 'active') {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', 'Your account has been ' . $agent->status . '.');
        }

        session()->regenerate();
        session()->put('user_id', $agent->id);
        session()->put('username', $agent->username);
        session()->put('role', $agent->role);
        session()->put('user_login_time', now()->timestamp);

        $this->loginTracker->logLogin(
            $agent->id,
            $request->ip(),
            $request->userAgent()
        );

        $agent->update(['last_login_ip' => $request->ip()]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Welcome back, ' . $agent->username . '!');
    }

    public function logout()
    {
        $userId = session('user_id');

        if ($userId) {
            $this->loginTracker->logLogout($userId);
        }

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out.');
    }
}
