<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Agent;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordService
    ) {}

    public function showForm()
    {
        return view('user.password.change');
    }

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
            $user = Agent::find(session('user_id'));

            if (! $user) {
                return redirect()->back()
                    ->with('error', 'User not found.');
            }

            if (! Hash::check($request->input('current_password'), $user->password_hash)) {
                return redirect()->back()
                    ->with('error', 'Current password is incorrect.');
            }

            $newHash = Hash::make($request->input('password'));

            $user->update([
                'password_hash' => $newHash,
                'updated_at' => now(),
            ]);

            // Sync password to admin account if user is an administrator
            if ($user->role === 'administrator') {
                AdminUser::where('username', $user->username)
                    ->orWhere('email', $user->email)
                    ->update(['password_hash' => $newHash]);
            }

            return redirect()->back()
                ->with('success', 'Password changed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to change password.');
        }
    }
}
