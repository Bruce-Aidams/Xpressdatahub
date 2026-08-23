<?php

namespace App\Services;

use App\Models\ApiPollingQueue;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'agent_id' => $data['agent_id'],
                'guest_id' => $data['guest_id'] ?? null,
                'phone_number' => $data['phone_number'] ?? $data['beneficiary_number'] ?? '',
                'network_type' => $data['network_type'],
                'package_size' => $data['package_size'],
                'amount' => $data['amount'],
                'status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'wallet',
                'transaction_id' => $data['transaction_id'] ?? null,
                'shop_id' => $data['shop_id'] ?? null,
                'order_source' => $data['order_source'] ?? 'agent',
                'order_reference' => $data['order_reference'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'base_amount' => $data['base_amount'] ?? $data['amount'],
                'paystack_total' => $data['paystack_total'] ?? $data['amount'],
            ]);

            return ['success' => true, 'order' => $order->toArray()];
        });
    }

    public function getOrder(int $id): ?array
    {
        $order = Order::with('agent:id,username,email,phone')
            ->find($id);

        return $order ? $order->toArray() : null;
    }

    public function getOrdersByAgent(int $agentId, array $filters = []): array
    {
        $query = Order::where('agent_id', $agentId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['network_type'])) {
            $query->where('network_type', $filters['network_type']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        return $query->orderByDesc('created_at')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function updateOrderStatus(int $orderId, string $status, ?string $notes = null, ?string $changedBy = 'system'): array
    {
        $order = Order::find($orderId);
        if (! $order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $oldStatus = $order->status;

        if ($oldStatus === $status) {
            return ['success' => true, 'message' => 'Status unchanged', 'old_status' => $oldStatus, 'new_status' => $status];
        }

        return DB::transaction(function () use ($order, $orderId, $status, $notes, $oldStatus, $changedBy) {
            $order->update([
                'status' => $status,
                'status_updated_at' => now(),
                'last_status_check' => now(),
            ]);

            DB::table('order_status_history')->insert([
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'notes' => $notes,
                'changed_by' => $changedBy,
                'created_at' => now(),
            ]);

            $this->handleStatusSideEffects($order, $oldStatus, $status);

            $this->cleanupPollingQueue($orderId, $status);

            return [
                'success' => true,
                'message' => 'Order status updated',
                'old_status' => $oldStatus,
                'new_status' => $status,
            ];
        });
    }

    public function getOrdersForStatusCheck(): array
    {
        return Order::whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function processOrderFromWebhook(array $data): array
    {
        $orderId = $data['order_id'] ?? null;
        $externalTransactionId = $data['external_transaction_id'] ?? null;
        $status = $data['status'] ?? null;

        if (! $orderId) {
            return ['success' => false, 'message' => 'Order ID is required'];
        }

        $order = Order::find($orderId);
        if (! $order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        return $this->updateOrderStatus($orderId, $status, 'Updated via webhook', 'webhook');
    }

    private function handleStatusSideEffects(Order $order, string $oldStatus, string $newStatus): void
    {
        try {
            if (in_array($newStatus, ['delivered', 'completed']) && ! in_array($oldStatus, ['delivered', 'completed'])) {
                $this->onOrderDelivered($order);
            }

            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $this->onOrderFailed($order);
            }

            if ($newStatus === 'processing' && $oldStatus !== 'processing') {
                ApiPollingQueue::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'status' => 'pending',
                        'attempts' => 0,
                        'max_attempts' => 10,
                        'next_attempt_at' => now()->addSeconds(30),
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("Side effects error for order #{$order->id}: {$e->getMessage()}");
        }
    }

    private function onOrderDelivered(Order $order): void
    {
        if ($order->agent_id) {
            try {
                $referralService = app(ReferralService::class);
                if ($referralService->isCommissionEnabled()) {
                    $referralService->calculateCommission([
                        'order_id' => $order->id,
                        'referred_user_id' => $order->agent_id,
                        'order_amount' => floatval($order->amount),
                        'order_date' => $order->created_at ? $order->created_at->format('Y-m-d') : date('Y-m-d'),
                        'triggered_by' => 'order_status_update',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Referral commission error for order #{$order->id}: {$e->getMessage()}");
            }
        }

        if ($order->shop_id && ($order->order_source ?? '') === 'shop') {
            try {
                $shopService = app(ShopService::class);
                $shopService->creditShopProfit((int) $order->shop_id, (int) $order->id);
            } catch (\Exception $e) {
                Log::error("Shop profit credit error for order #{$order->id}: {$e->getMessage()}");
            }
        }

        if ($order->agent_id) {
            try {
                $notificationService = app(NotificationService::class);
                $notificationService->sendNotification((int) $order->agent_id, 'order_success', [
                    'package_size' => $order->package_size,
                    'phone_number' => $order->phone_number,
                    'network' => $order->network_type,
                    'amount' => number_format(floatval($order->amount), 2),
                ]);
            } catch (\Exception $e) {
                Log::error("User notification error for order #{$order->id}: {$e->getMessage()}");
            }
        }
    }

    private function onOrderFailed(Order $order): void
    {
        try {
            $refundService = app(RefundService::class);
            $refundService->processRefund($order);
        } catch (\Exception $e) {
            Log::error("Refund trigger error for order #{$order->id}: {$e->getMessage()}");
        }

        try {
            $adminNotificationService = app(AdminNotificationService::class);
            $adminNotificationService->notifyAdmins([
                'title' => 'Order Failed',
                'message' => "Order #{$order->id} failed. Network: {$order->network_type}, Phone: {$order->phone_number}, Package: {$order->package_size}",
                'priority' => 'high',
            ]);
        } catch (\Exception $e) {
            Log::error("Admin notification error for order #{$order->id}: {$e->getMessage()}");
        }

        try {
            if ($order->agent_id) {
                $notificationService = app(NotificationService::class);
                $notificationService->sendNotification((int) $order->agent_id, 'order_failed', [
                    'package_size' => $order->package_size,
                    'phone_number' => $order->phone_number,
                    'network' => $order->network_type,
                    'amount' => number_format(floatval($order->amount), 2),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("User failure notification error for order #{$order->id}: {$e->getMessage()}");
        }
    }

    private function cleanupPollingQueue(int $orderId, string $status): void
    {
        if (in_array($status, ['delivered', 'completed', 'failed', 'cancelled'])) {
            ApiPollingQueue::where('order_id', $orderId)
                ->whereNotIn('status', ['completed', 'delivered'])
                ->update(['status' => 'completed']);
        }
    }
}
