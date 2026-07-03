<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAuthService;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class AdminPasswordController extends Controller
{
    public function __construct(
        private AdminAuthService $authService,
        private PasswordResetService $passwordService
    ) {}

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordCheck = $this->passwordService->validatePasswordStrength($request->input('password'));
        if (! $passwordCheck['valid']) {
            return redirect()->back()
                ->with('error', implode(' ', $passwordCheck['errors']));
        }

        try {
            $result = $this->authService->changePassword(
                session('admin_id'),
                $request->input('current_password'),
                $request->input('password')
            );

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Password changed successfully.');
            }

            return redirect()->back()
                ->with('error', $result['error'] ?? 'Failed to change password.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while changing password.');
        }
    }
}
