<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    /**
     * Process a refund for a failed order.
     *
     * @return bool True if refunded, false if not eligible or already refunded.
     */
    public function processRefund(Order $order): bool
    {
        if ($order->status !== 'failed') {
            Log::info("Refund skipped for Order #{$order->id}: Status is not failed ({$order->status}).");

            return false;
        }

        if ($order->is_refunded) {
            Log::info("Refund skipped for Order #{$order->id}: Already refunded.");

            return false;
        }

        // We only refund if the payment method was 'wallet'.
        if (! $order->agent_id || $order->payment_method !== 'wallet') {
            Log::info("Refund skipped for Order #{$order->id}: Not a wallet payment or no agent associated.");

            return false;
        }

        try {
            DB::transaction(function () use ($order) {
                // Refund the agent's balance
                $agent = Agent::lockForUpdate()->find($order->agent_id);
                if ($agent) {
                    $agent->increment('balance', $order->amount);

                    // Log balance history
                    BalanceHistoryService::log(
                        $agent->id,
                        floatval($order->amount),
                        'refund',
                        $order->id,
                        "Refund for failed order #{$order->id}"
                    );

                    // Mark order as refunded
                    $order->update(['is_refunded' => true]);

                    Log::info("Refund successful for Order #{$order->id}. Amount: GH₵{$order->amount}");
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Refund failed for Order #{$order->id}: ".$e->getMessage());

            return false;
        }
    }
}
