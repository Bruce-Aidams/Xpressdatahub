<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $resetService
    ) {}

    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        $agent = Agent::where('email', $email)->first();

        if (!$agent) {
            return redirect()->back()
                ->with('error', 'No account found with that email address.');
        }

        if (!$this->resetService->checkRateLimit($email)) {
            return redirect()->back()
                ->with('error', 'Too many reset attempts. Please try again later.');
        }

        $token = $this->resetService->generateToken();
        $otp = $this->resetService->generateOTP();

        $validation = $this->resetService->validatePasswordStrength($token);
        $expiresAt = now()->addMinutes(60);

        try {
            \App\Models\PasswordResetToken::create([
                'email' => $email,
                'token_hash' => password_hash($token, PASSWORD_DEFAULT),
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
                'max_attempts' => 5,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate reset token. Please try again.');
        }

        return redirect()->route('password.reset', ['token' => $token, 'email' => $email])
            ->with('success', 'A password reset link has been sent to your email.');
    }
}
