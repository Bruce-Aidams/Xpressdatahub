<?php

namespace App\Services;

use App\Models\BalanceHistory;
use App\Models\Agent;

class BalanceHistoryService
{
    public static function log(int $agentId, float $changeAmount, string $reason, ?int $refId = null, ?string $beneficiaryNumber = null): bool
    {
        $agent = Agent::select('balance')->find($agentId);
        if (!$agent) {
            report("BalanceHistory: Agent ID $agentId not found.");
            return false;
        }

        $balanceAfter = $agent->balance;

        if (empty($reason)) {
            $reason = 'txn';
        }

        $reasonMap = [
            'topup' => 'manual_adjustment',
            'order' => 'order',
            'payment' => 'payment',
            'refund' => 'manual_adjustment',
            'adjustment' => 'manual_adjustment',
            'bonus' => 'manual_adjustment',
            'commission' => 'manual_adjustment',
            'txn' => 'manual_adjustment',
        ];

        $mappedReason = $reasonMap[$reason] ?? 'manual_adjustment';

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
                'created_at' => now(),
            ]);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
