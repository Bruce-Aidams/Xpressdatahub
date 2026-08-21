<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebhookLog;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatusUpdateWebhookController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->all();
        $rawInput = $request->getContent();

        Log::info('Webhook received: status-update', [
            'payload' => $payload,
            'ip' => $request->ip(),
        ]);

        try {
            // ── 1. Extract status ─────────────────────────────────────
            $externalStatus = $payload['status']
                ?? $payload['order_status']
                ?? $payload['transaction_status']
                ?? null;

            if (! $externalStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required field: status',
                ], 400);
            }

            // ── 2. Map to internal status ─────────────────────────────
            $mappedStatus = $this->mapExternalStatus($externalStatus);

            if (! $mappedStatus) {
                return response()->json([
                    'success' => false,
                    'message' => "Unrecognised status value: {$externalStatus}",
                ], 422);
            }

            // ── 3. Resolve the local order ────────────────────────────
            $order = $this->resolveOrder($payload);

            if (! $order) {
                Log::warning('Webhook: order not found', ['payload' => $payload]);

                return response()->json([
                    'success' => false,
                    'message' => 'Order not found. Provide order_id, external_transaction_id, transaction_id, reference, or order_reference.',
                ], 404);
            }

            $oldStatus = $order->status;

            // ── 4. Bind external transaction ID if we have one ────────
            $externalTransactionId = $payload['external_transaction_id']
                ?? $payload['transaction_id']
                ?? $payload['transactionId']
                ?? $payload['reference']
                ?? null;

            if ($externalTransactionId && empty($order->external_transaction_id)) {
                $order->update(['external_transaction_id' => $externalTransactionId]);
            }

            // ── 5. Skip if status hasn't changed ──────────────────────
            if (strtolower($oldStatus) === strtolower($mappedStatus)) {
                $this->logWebhook($order->id, $externalTransactionId, $rawInput, $mappedStatus, false);

                return response()->json([
                    'success' => true,
                    'message' => 'Status unchanged',
                    'order_id' => $order->id,
                    'status' => $mappedStatus,
                ]);
            }

            // ── 6. Update the order status (triggers side-effects) ────
            $result = $this->orderService->updateOrderStatus(
                $order->id,
                $mappedStatus,
                "Updated via webhook (external status: {$externalStatus})",
                'webhook'
            );

            // Store raw response for audit trail
            if ($rawInput) {
                $order->update(['api_response_data' => $rawInput]);
            }

            $this->logWebhook($order->id, $externalTransactionId, $rawInput, $mappedStatus, true);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $mappedStatus,
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook processing error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'payload' => $payload,
            ]);
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Resolve the local Order from any identifier the external
     * provider may send. Tries many common field name conventions.
     */
    private function resolveOrder(array $payload): ?Order
    {
        // Our own local integer order ID
        if (! empty($payload['order_id'])) {
            $order = Order::find($payload['order_id']);
            if ($order) {
                return $order;
            }
        }

        // Our generated reference string (e.g. ORD-XXXXXXXX)
        if (! empty($payload['order_reference'])) {
            $order = Order::where('order_reference', $payload['order_reference'])->first();
            if ($order) {
                return $order;
            }
        }

        // External transaction / reference IDs from the provider
        $externalIds = array_values(array_filter([
            $payload['external_transaction_id'] ?? null,
            $payload['transaction_id'] ?? null,
            $payload['transactionId'] ?? null,
            $payload['reference'] ?? null,
            $payload['txn_id'] ?? null,
            $payload['txref'] ?? null,
        ]));

        foreach ($externalIds as $eid) {
            $order = Order::where('external_transaction_id', $eid)->first()
                ?? Order::where('transaction_id', $eid)->first()
                ?? Order::where('order_reference', $eid)->first();
            if ($order) {
                return $order;
            }
        }

        // Fuzzy match: phone + package size (most recent pending/processing order)
        $phone = $payload['phoneNumber'] ?? $payload['phone_number'] ?? $payload['phone'] ?? null;
        $package = $payload['packageSize'] ?? $payload['package_size'] ?? $payload['package'] ?? null;
        $amount = $payload['amount'] ?? null;

        if ($phone && $package) {
            $query = Order::where('phone_number', $phone)
                ->where('package_size', $package)
                ->whereIn('status', ['pending', 'processing'])
                ->orderByDesc('created_at');

            if ($amount) {
                $query->where('amount', $amount);
            }

            $order = $query->first();
            if ($order) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Map external provider status strings to our four internal statuses:
     * pending → processing → delivered | failed | cancelled
     */
    private function mapExternalStatus(?string $externalStatus): ?string
    {
        if (! $externalStatus) {
            return null;
        }

        $statusMap = [
            // → delivered
            'success' => 'delivered',
            'successful' => 'delivered',
            'delivered' => 'delivered',
            'completed' => 'delivered',
            'complete' => 'delivered',
            'fulfilled' => 'delivered',
            'done' => 'delivered',
            'sent' => 'delivered',

            // → processing
            'pending' => 'processing',
            'processing' => 'processing',
            'in_progress' => 'processing',
            'inprogress' => 'processing',
            'submitted' => 'processing',
            'queued' => 'processing',
            'initiated' => 'processing',

            // → failed
            'failed' => 'failed',
            'failure' => 'failed',
            'error' => 'failed',
            'unsuccessful' => 'failed',
            'rejected' => 'failed',
            'declined' => 'failed',

            // → cancelled
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'refunded' => 'cancelled',
            'reversed' => 'cancelled',
            'void' => 'cancelled',
            'voided' => 'cancelled',
        ];

        return $statusMap[strtolower(trim($externalStatus))] ?? null;
    }

    private function logWebhook(
        int $orderId,
        ?string $externalTransactionId,
        ?string $rawInput,
        string $status,
        bool $processed
    ): void {
        try {
            WebhookLog::create([
                'order_id' => $orderId,
                'webhook_type' => 'status_update',
                'external_transaction_id' => $externalTransactionId,
                'payload' => $rawInput,
                'response_status' => $status,
                'processed' => $processed,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
