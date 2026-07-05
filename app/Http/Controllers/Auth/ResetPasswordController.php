<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Agent;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $resetService
    ) {}

    public function showForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->query('email', $request->input('email'));

        if (! $token || ! $email) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid password reset link.');
        }

        return view('auth.reset-password', compact('token', 'email'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $token = $request->input('token');
        $email = $request->input('email');
        $password = $request->input('password');

        $passwordValidation = $this->resetService->validatePasswordStrength($password);
        if (! $passwordValidation['valid']) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', implode(' ', $passwordValidation['errors']));
        }

        $verified = $this->resetService->verifyToken($token, $email);

        if (! $verified) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Invalid or expired reset token.');
        }

        $agent = Agent::where('email', $email)->first();

        if (! $agent) {
            return redirect()->back()
                ->with('error', 'No account found with that email address.');
        }

        try {
            $newHash = Hash::make($password);

            $agent->update([
                'password_hash' => $newHash,
                'updated_at' => now(),
            ]);

            // Sync password to admin account if user is an administrator
            if ($agent->role === 'administrator') {
                AdminUser::where('username', $agent->username)
                    ->orWhere('email', $agent->email)
                    ->update(['password_hash' => $newHash]);
            }

            return redirect()->route('login')
                ->with('success', 'Password has been reset successfully. You can now log in.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reset password. Please try again.');
        }
    }
}
