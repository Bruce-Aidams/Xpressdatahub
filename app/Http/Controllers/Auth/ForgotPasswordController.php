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

    public function showOtpForm(Request $request)
    {
        return view('auth.otp-verification', ['email' => $request->query('email')]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $record = PasswordResetToken::where('email', $request->email)
            ->where('otp_code', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return redirect()->back()->with('error', 'Invalid or expired OTP.');
        }

        // Store OTP in session or somewhere to verify in reset form
        session(['otp_verified_email' => $request->email]);

        return redirect()->route('password.reset', ['email' => $request->email]);
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

        $otp = $this->resetService->generateOTP();
        $expiresAt = now()->addMinutes(10); // OTP expires quicker

        try {
            PasswordResetToken::create([
                'email' => $email,
                'token' => \Illuminate\Support\Facades\Hash::make('temporary'),
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate reset code. Please try again.');
        }

        // Send OTP email
        try {
            // Need a mailer that accepts OTP only or just reuse class if appropriate
            Mail::to($email)->send(new \App\Mail\ForgotPasswordMail(
                agentName: trim($agent->first_name . ' ' . $agent->last_name),
                token: '',
                email: $email,
                otp: $otp,
            ));
        } catch (\Exception $e) {
            Log::error('Forgot password email failed: ' . $e->getMessage());
        }

        return redirect()->route('password.otp', ['email' => $email])
            ->with('success', 'A verification code has been sent to your email.');
    }
}
