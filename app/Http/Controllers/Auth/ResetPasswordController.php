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
        $email = $request->query('email', $request->input('email'));
        $verifiedEmail = $request->session()->get('otp_verified_email');

        if (! $email || $email !== $verifiedEmail) {
            return redirect()->route('password.request')
                ->with('error', 'Please verify your email first.');
        }

        return view('auth.reset-password', compact('email'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        
        // Verify session just before reset
        if ($email !== $request->session()->get('otp_verified_email')) {
             return redirect()->route('password.request')
                ->with('error', 'Authentication expired. Please try again.');
        }

        $passwordValidation = $this->resetService->validatePasswordStrength($password);
        if (! $passwordValidation['valid']) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', implode(' ', $passwordValidation['errors']));
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

            $request->session()->forget('otp_verified_email');

            return redirect()->route('login')
                ->with('success', 'Password has been reset successfully. You can now log in.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reset password. Please try again.');
        }
    }
}
