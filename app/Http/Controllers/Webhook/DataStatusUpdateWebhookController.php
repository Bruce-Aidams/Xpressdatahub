<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WebhookLog;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataStatusUpdateWebhookController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $rawInput = $request->getContent();

            $this->logWebhook('data_status_update', $payload, $rawInput);

            $externalTransactionId = $payload['transaction_id']
                ?? $payload['external_transaction_id']
                ?? $payload['transactionId']
                ?? $payload['reference']
                ?? null;

            $status = $payload['status'] ?? null;

            if (! $externalTransactionId || ! $status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: transaction_id and status',
                ], 400);
            }

            $mappedStatus = $this->mapExternalStatus($status);
            if (! $mappedStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not map external status: '.$status,
                ], 422);
            }

            $order = $this->findOrder($externalTransactionId, $payload);

            if (! $order) {
                $order = $this->createOrderFromWebhook($payload, $externalTransactionId, $mappedStatus);
                if (! $order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found and could not create from webhook data',
                    ], 404);
                }
            }

            $oldStatus = $order->status;

            if (strtolower($oldStatus) === strtolower($mappedStatus)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status unchanged',
                    'order_id' => $order->id,
                ]);
            }

            $result = $this->orderService->updateOrderStatus(
                $order->id,
                $mappedStatus,
                'Updated via data provider webhook',
                'webhook'
            );

            $this->updateExternalData($order->id, $rawInput);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $mappedStatus,
            ]);

        } catch (\Exception $e) {
            Log::error("Data webhook processing error: {$e->getMessage()}");
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    private function findOrder(string $externalTransactionId, array $payload): ?Order
    {
        $order = Order::where('external_transaction_id', $externalTransactionId)->first();
        if ($order) {
            return $order;
        }

        $order = Order::where('transaction_id', $externalTransactionId)->first();
        if ($order) {
            return $order;
        }

        $order = Order::where('order_reference', $externalTransactionId)->first();
        if ($order) {
            return $order;
        }

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

            if ($order && empty($order->external_transaction_id)) {
                $order->update(['external_transaction_id' => $externalTransactionId]);
            }

            return $order;
        }

        return null;
    }

    private function createOrderFromWebhook(array $payload, string $externalTransactionId, string $mappedStatus): ?Order
    {
        $phoneNumber = $payload['phoneNumber'] ?? $payload['phone_number'] ?? null;
        $packageSize = $payload['packageSize'] ?? $payload['package_size'] ?? null;
        $amount = $payload['amount'] ?? 0;
        $username = $payload['username'] ?? null;

        if (! $phoneNumber || ! $packageSize || ! $username) {
            return null;
        }

        $agent = null;
        if ($username) {
            $agent = DB::table('agents')->where('username', $username)->first();
        }

        if (! $agent) {
            return null;
        }

        $networkType = $payload['network_type'] ?? $this->detectNetworkType($phoneNumber);

        $order = Order::create([
            'agent_id' => $agent->id,
            'phone_number' => $phoneNumber,
            'network_type' => $networkType,
            'package_size' => $packageSize,
            'amount' => $amount,
            'status' => $mappedStatus,
            'order_source' => 'webhook',
            'external_transaction_id' => $externalTransactionId,
            'status_updated_at' => now(),
        ]);

        DB::table('order_status_history')->insert([
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => $mappedStatus,
            'notes' => 'Order created from webhook data',
            'changed_by' => 'webhook',
            'created_at' => now(),
        ]);

        return $order;
    }

    private function detectNetworkType(string $phoneNumber): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        $prefix = substr($phone, 0, 3);

        $networkPrefixes = [
            'MTN' => ['024', '025', '053', '054', '055', '059'],
            'Telecel' => ['020', '050'],
            'AirtelTigo' => ['027', '057', '026', '056', '023'],
        ];

        foreach ($networkPrefixes as $network => $prefixes) {
            if (in_array($prefix, $prefixes)) {
                return $network;
            }
        }

        return 'MTN';
    }

    private function updateExternalData(int $orderId, ?string $rawInput): void
    {
        if ($rawInput) {
            Order::where('id', $orderId)->update([
                'api_response_data' => $rawInput,
            ]);
        }
    }

    private function mapExternalStatus(?string $externalStatus): ?string
    {
        if (! $externalStatus) {
            return null;
        }

        $statusMap = [
            'success' => 'delivered',
            'successful' => 'delivered',
            'delivered' => 'delivered',
            'completed' => 'delivered',
            'failed' => 'failed',
            'failure' => 'failed',
            'error' => 'failed',
            'unsuccessful' => 'failed',
            'pending' => 'processing',
            'processing' => 'processing',
            'in_progress' => 'processing',
            'submitted' => 'processing',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        return $statusMap[strtolower(trim($externalStatus))] ?? null;
    }

    private function logWebhook(string $type, array $payload, ?string $rawInput = null): void
    {
        try {
            WebhookLog::create([
                'webhook_type' => $type,
                'payload' => $rawInput ?? json_encode($payload, JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
