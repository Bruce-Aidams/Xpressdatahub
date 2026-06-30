<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AdminAuthService;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function __construct(
        private AdminAuthService $authService
    ) {}

    public function showLoginForm()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $this->authService->authenticate(
            $request->input('username'),
            $request->input('password')
        );

        if ($result['success']) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $result['admin']['username'] . '!');
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', $result['error'] ?? 'Invalid credentials.');
    }

    public function logout()
    {
        $this->authService->logout();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out.');
    }
}
