<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\DB;

class AccountStatusManager
{
    public function updateAccountStatus(int $userId, string $newStatus, string $reason = ''): array
    {
        $validStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($newStatus, $validStatuses, true)) {
            return ['success' => false, 'message' => 'Invalid account status'];
        }

        $agent = Agent::find($userId);
        if (!$agent) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $currentStatus = $agent->status ?? 'active';
        if ($currentStatus === $newStatus) {
            return [
                'success' => true,
                'message' => 'Status is already ' . $newStatus,
                'action' => 'no_change',
            ];
        }

        return DB::transaction(function () use ($userId, $newStatus, $reason, $currentStatus) {
            Agent::where('id', $userId)->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            DB::table('account_status_history')->insert([
                'user_id' => $userId,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Account status updated successfully',
                'action' => 'updated',
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
            ];
        });
    }

    public function getAccountStatus(int $userId): ?array
    {
        $agent = Agent::find($userId);
        return $agent ? $agent->toArray() : null;
    }

    public function canUserLogin(int $userId): bool
    {
        $agent = Agent::select('status')->find($userId);
        return $agent && ($agent->status === 'active');
    }

    public function bulkUpdateAccountStatus(array $userIds, string $newStatus, string $reason = ''): array
    {
        $successCount = 0;
        $errorCount = 0;
        $results = [];

        foreach ($userIds as $userId) {
            $result = $this->updateAccountStatus($userId, $newStatus, $reason);
            $results[] = ['user_id' => $userId, 'result' => $result];

            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results,
        ];
    }

    public function autoSuspendAccount(int $userId, string $reason = ''): array
    {
        $threshold = (int) DB::table('payment_config')
            ->where('config_key', 'account_auto_suspend_failed_logins')
            ->value('config_value') ?? 5;

        return $this->updateAccountStatus(
            $userId,
            'suspended',
            $reason ?: 'Auto-suspended due to multiple failed login attempts'
        );
    }
}
