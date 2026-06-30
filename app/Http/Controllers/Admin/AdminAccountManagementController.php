<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\AccountStatusManager;
use Illuminate\Http\Request;

class AdminAccountManagementController extends Controller
{
    public function __construct(
        private AccountStatusManager $statusManager
    ) {}

    public function index(Request $request)
    {
        $query = Agent::query();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderByDesc('created_at')->paginate(25);

        return view('admin.accounts.index', compact('accounts'));
    }

    public function updateStatus(Request $request, Agent $agent)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,suspended',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->statusManager->updateAccountStatus(
                $agent->id,
                $request->input('status'),
                $request->input('reason', 'Updated by admin')
            );

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Account status updated successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to update account status.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating account status.');
        }
    }
}
