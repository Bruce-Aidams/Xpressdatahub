<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserPasswordController extends Controller
{
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

        try {
            $user = Agent::find(session('user_id'));

            if (!$user) {
                return redirect()->back()
                    ->with('error', 'User not found.');
            }

            if (!Hash::check($request->input('current_password'), $user->password_hash)) {
                return redirect()->back()
                    ->with('error', 'Current password is incorrect.');
            }

            $user->update([
                'password_hash' => Hash::make($request->input('password')),
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Password changed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to change password.');
        }
    }
}
