<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserApiKeyController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        $apiKeys = ApiKey::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('user.api-keys.index', compact('apiKeys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $apiKey = ApiKey::create([
                'user_id' => session('user_id'),
                'name' => $request->input('name'),
                'api_key' => 'mk_' . Str::random(32),
                'api_secret' => Str::random(64),
                'is_active' => true,
                'rate_limit' => 100,
                'permissions' => ['orders:create', 'orders:read'],
            ]);

            return redirect()->back()
                ->with('success', 'API key created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create API key.');
        }
    }

    public function destroy(ApiKey $apiKey)
    {
        $userId = session('user_id');

        if ($apiKey->user_id !== $userId) {
            return redirect()->back()
                ->with('error', 'Unauthorized action.');
        }

        try {
            $apiKey->delete();

            return redirect()->back()
                ->with('success', 'API key deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete API key.');
        }
    }
}
