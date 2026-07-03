<?php

namespace App\Console\Commands;

use App\Models\ApiPollingQueue;
use App\Models\Order;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollOrderStatus extends Command
{
    protected $signature = 'api:poll-status {--limit=50 : Max orders to process per run}';

    protected $description = 'Poll external APIs for order status updates and auto-update local order status';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $items = ApiPollingQueue::whereIn('status', ['pending', 'processing'])
            ->where('next_attempt_at', '<=', now())
            ->orderBy('next_attempt_at')
            ->limit($limit)
            ->get();

        if ($items->isEmpty()) {
            $this->info('No orders to poll.');

            return static::SUCCESS;
        }

        $this->info("Polling {$items->count()} order(s) for status updates...");
        $this->newLine();

        $successCount = 0;
        $failedCount = 0;
        $pendingCount = 0;
        $skippedCount = 0;

        foreach ($items as $item) {
            $result = $this->pollOrder($item);

            match ($result) {
                'completed' => $successCount++,
                'failed' => $failedCount++,
                'pending' => $pendingCount++,
                default => $skippedCount++,
            };
        }

        $this->newLine();
        $this->info("Done: {$successCount} completed, {$failedCount} failed, {$pendingCount} still pending, {$skippedCount} skipped");

        return static::SUCCESS;
    }

    private function pollOrder(ApiPollingQueue $item): string
    {
        try {
            $order = Order::find($item->order_id);
            if (! $order) {
                $this->warn("Order #{$item->order_id} not found, skipping.");
                $item->update(['status' => 'failed', 'attempts' => $item->max_attempts]);

                return 'skipped';
            }

            if (in_array($order->status, ['delivered', 'completed', 'failed', 'cancelled'])) {
                $item->update(['status' => 'completed']);

                return 'skipped';
            }

            if ($item->attempts >= $item->max_attempts) {
                $this->warn("Order #{$order->id} reached max attempts ({$item->max_attempts}), marking failed.");

                $this->orderService()->updateOrderStatus(
                    $order->id,
                    'failed',
                    'Max polling attempts reached',
                    'polling'
                );

                $item->update(['status' => 'completed']);

                return 'failed';
            }

            $apiService = new ExternalApiService($order->network_type);
            $transactionId = $order->external_transaction_id ?? $order->transaction_id ?? $order->order_reference;

            if (! $transactionId) {
                $this->warn("Order #{$order->id} has no transaction ID, skipping.");
                $item->update(['status' => 'completed']);

                return 'skipped';
            }

            $this->info("Polling order #{$order->id} ({$order->network_type}) - attempt ".($item->attempts + 1)."/{$item->max_attempts}");

            $apiResult = $apiService->checkTransactionStatus($transactionId);

            $externalStatus = $this->extractStatus($apiResult);

            $item->update([
                'attempts' => $item->attempts + 1,
                'last_attempt_at' => now(),
            ]);

            if ($externalStatus === null) {
                $this->warn('  Could not determine status from API response.');
                $this->scheduleNextAttempt($item);

                return 'pending';
            }

            $mappedStatus = $this->mapExternalStatus($externalStatus);

            if ($mappedStatus === null) {
                $this->warn("  Could not map external status: {$externalStatus}");
                $this->scheduleNextAttempt($item);

                return 'pending';
            }

            if (in_array($mappedStatus, ['delivered', 'completed', 'failed', 'cancelled'])) {
                $apiResponseJson = json_encode($apiResult['data'] ?? []);

                $this->orderService()->updateOrderStatus(
                    $order->id,
                    $mappedStatus,
                    "Status updated via polling (HTTP {$apiResult['http_code']})",
                    'polling'
                );

                if ($apiResponseJson && $apiResponseJson !== 'null') {
                    $order->update(['api_response_data' => $apiResponseJson]);
                }

                $item->update(['status' => 'completed']);

                $this->info("  Order #{$order->id} updated: {$order->status} → {$mappedStatus}");

                return 'completed';
            }

            $this->scheduleNextAttempt($item);
            $this->info("  Order #{$order->id} still {$mappedStatus}, will retry.");

            return 'pending';

        } catch (\Exception $e) {
            Log::error("Polling error for order #{$item->order_id}: {$e->getMessage()}");
            $this->error("  Error polling order #{$item->order_id}: {$e->getMessage()}");
            $this->scheduleNextAttempt($item);

            return 'skipped';
        }
    }

    private function extractStatus(array $apiResult): ?string
    {
        if (! empty($apiResult['data'])) {
            $data = $apiResult['data'];

            $statusFields = ['status', 'state', 'result', 'delivery_status', 'transaction_status'];
            foreach ($statusFields as $field) {
                if (isset($data[$field]) && is_string($data[$field])) {
                    return $data[$field];
                }
            }

            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($statusFields as $field) {
                    if (isset($data['data'][$field]) && is_string($data['data'][$field])) {
                        return $data['data'][$field];
                    }
                }
            }
        }

        if (! $apiResult['success'] && ! empty($apiResult['error'])) {
            return null;
        }

        return null;
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
            'queued' => 'processing',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        return $statusMap[strtolower(trim($externalStatus))] ?? null;
    }

    private function scheduleNextAttempt(ApiPollingQueue $item): void
    {
        $attempts = $item->attempts + 1;
        $baseDelay = 30;
        $delaySeconds = min($baseDelay * pow(2, $attempts), 1800);

        $item->update([
            'next_attempt_at' => now()->addSeconds($delaySeconds),
        ]);
    }

    private function orderService(): OrderService
    {
        return app(OrderService::class);
    }
}
