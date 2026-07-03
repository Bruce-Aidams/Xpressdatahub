<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = Agent::withCount('orders')->find(session('user_id'));

        view()->share('currentUser', $user);

        return view('user.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:agents,email,'.session('user_id'),
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            $user = Agent::find(session('user_id'));

            if (! $user) {
                return redirect()->back()
                    ->with('error', 'User not found.');
            }

            $user->update($request->only([
                'first_name', 'last_name', 'email', 'phone',
            ]));

            view()->share('currentUser', $user);

            return redirect()->back()
                ->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update profile.');
        }
    }
}
