<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAuthService;
use Illuminate\Http\Request;

class AdminPasswordController extends Controller
{
    public function __construct(
        private AdminAuthService $authService
    ) {}

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

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
