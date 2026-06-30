<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminProfileController extends Controller
{
    public function show()
    {
        $admin = AdminUser::find(session('admin_id'));

        return view('admin.profile.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:admin_users,email,' . session('admin_id'),
        ]);

        try {
            $admin = AdminUser::find(session('admin_id'));

            if (!$admin) {
                return redirect()->back()
                    ->with('error', 'Admin not found.');
            }

            $admin->update([
                'full_name' => $request->input('full_name', $admin->full_name),
                'email' => $request->input('email', $admin->email),
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update profile.');
        }
    }
}
