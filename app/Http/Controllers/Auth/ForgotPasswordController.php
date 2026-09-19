<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\Agent;
use App\Models\PasswordResetToken;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        if (! $agent) {
            return redirect()->back()
                ->with('error', 'No account found with that email address.');
        }

        if (! $this->resetService->checkRateLimit($email)) {
            return redirect()->back()
                ->with('error', 'Too many reset attempts. Please try again later.');
        }

        $token = $this->resetService->generateToken();
        $otp = $this->resetService->generateOTP();

        $validation = $this->resetService->validatePasswordStrength($token);
        $expiresAt = now()->addMinutes(60);

        try {
            PasswordResetToken::create([
                'email' => $email,
                'token' => \Illuminate\Support\Facades\Hash::make($token),
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate reset token. Please try again.');
        }

        // Send password reset email
        try {
            Mail::to($email)->send(new ForgotPasswordMail(
                agentName: trim($agent->first_name . ' ' . $agent->last_name),
                token: $token,
                email: $email,
                otp: $otp,
            ));
        } catch (\Exception $e) {
            Log::error('Forgot password email failed: ' . $e->getMessage());
        }

        return redirect()->route('password.reset', ['token' => $token, 'email' => $email])
            ->with('success', 'A password reset email has been sent to your inbox.');
    }
}
