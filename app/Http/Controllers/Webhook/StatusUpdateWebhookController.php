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
        try {
            $payload = $request->all();
            $rawInput = $request->getContent();

            $orderId = $payload['order_id'] ?? null;
            $status = $payload['status'] ?? null;
            $externalTransactionId = $payload['external_transaction_id']
                ?? $payload['transaction_id']
                ?? $payload['transactionId']
                ?? $payload['reference']
                ?? null;

            if (! $orderId && ! $externalTransactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: order_id or external_transaction_id',
                ], 400);
            }

            if (! $status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required field: status',
                ], 400);
            }

            $validStatuses = ['pending', 'processing', 'completed', 'delivered', 'failed', 'cancelled'];
            if (! in_array(strtolower($status), $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status value: '.$status,
                ], 422);
            }

            $order = null;
            if ($orderId) {
                $order = Order::find($orderId);
            }

            if (! $order && $externalTransactionId) {
                $order = Order::where('external_transaction_id', $externalTransactionId)->first()
                    ?? Order::where('transaction_id', $externalTransactionId)->first()
                    ?? Order::where('order_reference', $externalTransactionId)->first();
            }

            if (! $order) {
                $phoneNumber = $payload['phoneNumber'] ?? $payload['phone_number'] ?? null;
                $packageSize = $payload['packageSize'] ?? $payload['package_size'] ?? null;
                $amount = $payload['amount'] ?? null;
                $username = $payload['username'] ?? null;

                if ($phoneNumber && $packageSize && $amount && $username) {
                    $order = Order::join('agents', 'agents.id', '=', 'orders.agent_id')
                        ->where('orders.phone_number', $phoneNumber)
                        ->where('orders.package_size', $packageSize)
                        ->where('orders.amount', $amount)
                        ->where('agents.username', $username)
                        ->orderByDesc('orders.created_at')
                        ->limit(1)
                        ->select('orders.*')
                        ->first();

                    if ($order && $externalTransactionId && empty($order->external_transaction_id)) {
                        $order->update(['external_transaction_id' => $externalTransactionId]);
                    }
                }
            }

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $oldStatus = $order->status;
            $mappedStatus = $this->mapExternalStatus($status);

            if (! $mappedStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not map status: '.$status,
                ], 422);
            }

            if (strtolower($oldStatus) === strtolower($mappedStatus)) {
                $this->logWebhook($order->id, $externalTransactionId, $rawInput, $mappedStatus);

                return response()->json([
                    'success' => true,
                    'message' => 'Status unchanged',
                    'order_id' => $order->id,
                ]);
            }

            $result = $this->orderService->updateOrderStatus(
                $order->id,
                $mappedStatus,
                'Updated via status webhook',
                'webhook'
            );

            if ($externalTransactionId && empty($order->external_transaction_id)) {
                $order->update(['external_transaction_id' => $externalTransactionId]);
            }

            if ($rawInput) {
                $order->update(['api_response_data' => $rawInput]);
            }

            $this->logWebhook($order->id, $externalTransactionId, $rawInput, $mappedStatus);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $mappedStatus,
            ]);

        } catch (\Exception $e) {
            Log::error("Status webhook processing error: {$e->getMessage()}");
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    private function mapExternalStatus(?string $externalStatus): ?string
    {
        if (! $externalStatus) {
            return null;
        }

        $statusMap = [
            'completed' => 'delivered',
            'success' => 'delivered',
            'delivered' => 'delivered',
            'successful' => 'delivered',
            'failed' => 'failed',
            'error' => 'failed',
            'unsuccessful' => 'failed',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'refunded' => 'cancelled',
            'pending' => 'processing',
            'processing' => 'processing',
            'in_progress' => 'processing',
            'submitted' => 'processing',
        ];

        return $statusMap[strtolower(trim($externalStatus))] ?? null;
    }

    private function logWebhook(int $orderId, ?string $externalTransactionId, ?string $rawInput, string $status): void
    {
        try {
            WebhookLog::create([
                'order_id' => $orderId,
                'webhook_type' => 'status_update',
                'external_transaction_id' => $externalTransactionId,
                'payload' => $rawInput,
                'response_status' => $status,
                'processed' => true,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
