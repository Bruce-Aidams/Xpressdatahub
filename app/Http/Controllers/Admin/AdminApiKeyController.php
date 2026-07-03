<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminApiKeyController extends Controller
{
    public function index()
    {
        $apiKeys = ApiKey::with('agent:id,username')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.api-keys.index', compact('apiKeys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:agents,id',
            'rate_limit' => 'nullable|integer|min:1',
            'permissions' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $apiKey = ApiKey::create([
                'user_id' => $request->input('user_id'),
                'name' => $request->input('name'),
                'api_key' => 'mk_'.Str::random(32),
                'api_secret' => Str::random(64),
                'is_active' => true,
                'rate_limit' => $request->input('rate_limit', 100),
                'permissions' => $request->input('permissions', ['orders:create', 'orders:read']),
                'expires_at' => $request->input('expires_at'),
            ]);

            return redirect()->back()
                ->with('success', 'API key created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create API key.');
        }
    }

    public function update(Request $request, ApiKey $apiKey)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'rate_limit' => 'nullable|integer|min:1',
            'permissions' => 'nullable|array',
        ]);

        try {
            $apiKey->update($request->only([
                'name', 'is_active', 'rate_limit', 'permissions',
            ]));

            return redirect()->back()
                ->with('success', 'API key updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update API key.');
        }
    }

    public function destroy(ApiKey $apiKey)
    {
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
