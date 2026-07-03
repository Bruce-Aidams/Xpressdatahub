<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\PasswordResetService;
use App\Services\ReferralService;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(
        private ReferralService $referralService,
        private ShopService $shopService,
        private PasswordResetService $passwordService
    ) {}

    public function showRegistrationForm()
    {
        if (session('user_id')) {
            return redirect()->route('user.dashboard');
        }

        $referralCode = request('ref');

        return view('auth.register', compact('referralCode'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:agents,username',
            'email' => 'required|email|max:255|unique:agents,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'nullable|string',
        ]);

        $passwordCheck = $this->passwordService->validatePasswordStrength($request->input('password'));
        if (! $passwordCheck['valid']) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', implode(' ', $passwordCheck['errors']));
        }

        try {
            $agent = Agent::create([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password_hash' => Hash::make($request->input('password')),
                'role' => 'agent',
                'status' => 'active',
                'is_approved' => false,
                'registration_ip' => $request->ip(),
                'referral_code' => $this->referralService->generateReferralCode(0),
            ]);

            $this->referralService->generateReferralCode($agent->id);

            $referralCode = $request->input('referral_code');
            if ($referralCode) {
                $this->referralService->trackReferral($agent->id, $referralCode);
            }

            $this->shopService->createShopForUser($agent->id, $agent->username);

            return redirect()->route('pending.approval')
                ->with('success', 'Account created successfully! Please wait for admin approval.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}
