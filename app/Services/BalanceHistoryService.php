<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\BalanceHistory;

class BalanceHistoryService
{
    public static function log(int $agentId, float $changeAmount, string $reason, ?int $refId = null, ?string $beneficiaryNumber = null, ?string $description = null): bool
    {
        $agent = Agent::select('balance')->find($agentId);
        if (! $agent) {
            report("BalanceHistory: Agent ID $agentId not found.");

            return false;
        }

        $balanceAfter = $agent->balance;

        if (empty($reason)) {
            $reason = 'txn';
        }

        $reasonMap = [
            'topup' => 'topup',
            'order' => 'order',
            'payment' => 'payment',
            'refund' => 'refund',
            'adjustment' => 'adjustment',
            'bonus' => 'bonus',
            'commission' => 'commission',
            'cart_order' => 'order',
            'txn' => 'txn',
        ];

        $mappedReason = $reasonMap[$reason] ?? $reason;

        if ($beneficiaryNumber && strlen($beneficiaryNumber) > 10) {
            $beneficiaryNumber = substr($beneficiaryNumber, 0, 10);
        }

        try {
            BalanceHistory::create([
                'agent_id' => $agentId,
                'change_amount' => $changeAmount,
                'balance_after' => $balanceAfter,
                'reason' => $mappedReason,
                'reference_id' => $refId,
                'beneficiary_number' => $beneficiaryNumber,
                'description' => $description,
                'created_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }
}
