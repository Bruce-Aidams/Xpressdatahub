<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\AccountStatusManager;
use App\Services\BalanceHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAccountManagementController extends Controller
{
    public function __construct(
        private AccountStatusManager $statusManager
    ) {}

    public function index(Request $request)
    {
        $superAdminUsernames = \App\Models\AdminUser::where('role', 'super_admin')->pluck('username')->toArray();
        $query = Agent::whereNotIn('username', $superAdminUsernames);

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
        $isSuperAdmin = \App\Models\AdminUser::where('username', $agent->username)->where('role', 'super_admin')->exists();
        if ($isSuperAdmin) {
            return redirect()->back()
                ->with('error', 'You cannot suspend or modify the super admin.');
        }

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

    public function bulkCredit(Request $request)
    {
        $request->validate([
            'agent_ids' => 'required|array|min:1',
            'agent_ids.*' => 'integer|exists:agents,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $agentIds = $request->input('agent_ids');
        $amount = (float) $request->input('amount');
        $description = $request->input('description', 'Admin credit');
        $credited = 0;

        try {
            DB::beginTransaction();

            foreach ($agentIds as $agentId) {
                $agent = Agent::find($agentId);
                if (! $agent) {
                    continue;
                }

                $agent->increment('balance', $amount);

                BalanceHistoryService::log(
                    $agentId,
                    $amount,
                    'adjustment',
                    null,
                    null,
                    $description
                );

                $credited++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'GH&#8373;'.number_format($amount, 2)." credited to {$credited} account(s) successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while processing credit.');
        }
    }

    public function bulkDebit(Request $request)
    {
        $request->validate([
            'agent_ids' => 'required|array|min:1',
            'agent_ids.*' => 'integer|exists:agents,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $agentIds = $request->input('agent_ids');
        $amount = (float) $request->input('amount');
        $description = $request->input('description', 'Admin debit');

        $insufficient = [];
        foreach ($agentIds as $agentId) {
            $agent = Agent::find($agentId);
            if ($agent && $agent->balance < $amount) {
                $insufficient[] = $agent->username;
            }
        }

        if (! empty($insufficient)) {
            return redirect()->back()
                ->with('error', 'Insufficient balance for: '.implode(', ', $insufficient).'. Debit cancelled.');
        }

        $debited = 0;

        try {
            DB::beginTransaction();

            foreach ($agentIds as $agentId) {
                $agent = Agent::find($agentId);
                if (! $agent) {
                    continue;
                }

                $agent->decrement('balance', $amount);

                BalanceHistoryService::log(
                    $agentId,
                    -$amount,
                    'adjustment',
                    null,
                    null,
                    $description
                );

                $debited++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'GH&#8373;'.number_format($amount, 2)." debited from {$debited} account(s) successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while processing debit.');
        }
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'agent_ids' => 'required|array|min:1',
            'agent_ids.*' => 'integer|exists:agents,id',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $agentIds = $request->input('agent_ids');
        $status = $request->input('status');

        try {
            $result = $this->statusManager->bulkUpdateAccountStatus(
                $agentIds,
                $status,
                'Bulk update by admin'
            );

            $updated = $result['success_count'];

            return redirect()->back()
                ->with('success', ucfirst($status)." status applied to {$updated} account(s) successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating account statuses.');
        }
    }
}
