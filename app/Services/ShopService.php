<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agent;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopEarning;
use App\Models\ShopPricing;
use App\Models\ShopSetting;
use App\Models\ShopWithdrawal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShopService
{
    private CustomPricingService $customPricingService;

    public function __construct(CustomPricingService $customPricingService)
    {
        $this->customPricingService = $customPricingService;
    }

    public function createShopForUser(int $userId, string $username): ?array
    {
        $existing = Shop::where('user_id', $userId)->first();
        if ($existing) {
            return $existing->toArray();
        }

        $slug = $this->generateUniqueSlug($username);

        return DB::transaction(function () use ($userId, $slug) {
            $shop = Shop::create([
                'user_id' => $userId,
                'shop_slug' => $slug,
                'is_active' => false,
            ]);

            ShopSetting::create([
                'shop_id' => $shop->id,
                'working_hours' => $this->defaultWorkingHoursJson(),
            ]);

            $agent = Agent::select('role')->find($userId);
            $role = $agent->role ?? 'agent';
            $this->initializeDefaultPricing($shop->id, $role);

            return Shop::with('agent')->find($shop->id)->toArray();
        });
    }

    public function getShopByUserId(int $userId): ?array
    {
        $shop = Shop::where('user_id', $userId)->first();

        return $shop ? $shop->toArray() : null;
    }

    public function getShopBySlug(string $slug): ?array
    {
        $shop = Shop::with('agent:id,username,role')
            ->where('shop_slug', $slug)
            ->first();

        return $shop ? $shop->toArray() : null;
    }

    public function saveShopSettings(int $shopId, array $data): bool
    {
        $hours = $data['working_hours'] ?? null;
        $hoursJson = is_array($hours) ? json_encode($hours) : (is_string($hours) ? $hours : null);
        $wa = trim($data['whatsapp_number'] ?? '');
        $waLink = trim($data['whatsapp_group_link'] ?? '');

        ShopSetting::updateOrCreate(
            ['shop_id' => $shopId],
            [
                'working_hours' => $hoursJson,
                'whatsapp_number' => $wa ?: null,
                'whatsapp_group_link' => $waLink ?: null,
            ]
        );

        return true;
    }

    public function listShopPricing(int $shopId): Collection
    {
        return ShopPricing::where('shop_id', $shopId)
            ->orderBy('network_type')
            ->orderBy('package_size_gb')
            ->get();
    }

    public function updateShopPricing(int $shopId, int $pricingId, array $data): array
    {
        $pricing = ShopPricing::where('shop_id', $shopId)
            ->where('id', $pricingId)
            ->first();

        if (! $pricing) {
            return ['success' => false, 'message' => 'Package not found'];
        }

        $base = floatval($pricing->base_price);
        $sell = floatval($data['selling_price'] ?? 0);

        if ($sell < $base) {
            return ['success' => false, 'message' => 'Selling price cannot be below base price ('.$base.' GHS)'];
        }

        $profit = round($sell - $base, 2);

        $pricing->update([
            'selling_price' => $sell,
            'profit' => $profit,
        ]);

        return ['success' => true];
    }

    public function recordShopEarning(array $data): bool
    {
        $orderId = $data['order_id'];

        $exists = ShopEarning::where('order_id', $orderId)->exists();
        if ($exists) {
            return false;
        }

        $sellingPrice = floatval($data['selling_price']);
        $basePrice = floatval($data['base_price']);
        $profit = round($sellingPrice - $basePrice, 2);

        ShopEarning::create([
            'shop_id' => $data['shop_id'],
            'order_id' => $orderId,
            'order_reference' => $data['order_reference'] ?? '',
            'package_size' => $data['package_size'] ?? '',
            'selling_price' => $sellingPrice,
            'base_price' => $basePrice,
            'profit' => $profit,
            'status' => 'pending',
        ]);

        return true;
    }

    public function creditShopProfit(int $shopId, int $orderId): bool
    {
        return ShopEarning::where('shop_id', $shopId)
            ->where('order_id', $orderId)
            ->where('status', 'pending')
            ->update(['status' => 'credited', 'credited_at' => now()]) > 0;
    }

    public function getShopEarningsSummary(int $shopId): array
    {
        $credited = ShopEarning::where('shop_id', $shopId)
            ->where('status', 'credited')
            ->sum('profit');

        $pending = ShopEarning::where('shop_id', $shopId)
            ->where('status', 'pending')
            ->sum('profit');

        $reserved = ShopWithdrawal::where('shop_id', $shopId)
            ->whereIn('status', ['pending', 'approved', 'completed', 'delivered'])
            ->sum('amount');

        $completedOut = ShopWithdrawal::where('shop_id', $shopId)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('amount');

        $available = round(floatval($credited) - floatval($reserved), 2);

        return [
            'credited_profit' => floatval($credited),
            'pending_profit' => floatval($pending),
            'withdrawn_or_reserved' => floatval($reserved),
            'completed_withdrawals' => floatval($completedOut),
            'available_balance' => max(0, $available),
        ];
    }

    public function requestWithdrawal(int $shopId, int $userId, float $amount, string $method, string $details): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Invalid amount'];
        }

        $hasPending = ShopWithdrawal::where('shop_id', $shopId)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return ['success' => false, 'message' => 'You already have a pending withdrawal request'];
        }

        $min = floatval(DB::table('payment_config')->where('config_key', 'shop_min_withdrawal')->value('config_value') ?? 10);
        $max = floatval(DB::table('payment_config')->where('config_key', 'shop_max_withdrawal')->value('config_value') ?? 100000);

        if ($amount < $min) {
            return ['success' => false, 'message' => 'Minimum withdrawal is GH₵ '.number_format($min, 2)];
        }

        if ($max > 0 && $amount > $max) {
            return ['success' => false, 'message' => 'Maximum withdrawal is GH₵ '.number_format($max, 2)];
        }

        $summary = $this->getShopEarningsSummary($shopId);
        if ($amount > $summary['available_balance'] + 0.009) {
            return ['success' => false, 'message' => 'Amount exceeds available shop profit'];
        }

        ShopWithdrawal::create([
            'shop_id' => $shopId,
            'user_id' => $userId,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_details' => $details,
            'status' => 'pending',
        ]);

        return ['success' => true];
    }

    public function approveWithdrawal(int $withdrawalId, ?string $adminNote = null): bool
    {
        return ShopWithdrawal::where('id', $withdrawalId)
            ->where('status', 'pending')
            ->update(['status' => 'approved', 'admin_note' => $adminNote]) > 0;
    }

    public function rejectWithdrawal(int $withdrawalId, ?string $adminNote = null): bool
    {
        return ShopWithdrawal::where('id', $withdrawalId)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'rejected', 'admin_note' => $adminNote]) > 0;
    }

    public function completeWithdrawal(int $withdrawalId): bool
    {
        return ShopWithdrawal::where('id', $withdrawalId)
            ->where('status', 'approved')
            ->update(['status' => 'completed']) > 0;
    }

    public function adminFinalizeShopOrderPayment(int $orderId, ?int $adminId, string $adminUsername, ?string $paystackTxnId = null): array
    {
        if ($orderId < 1) {
            return ['success' => false, 'message' => 'Invalid order'];
        }

        return DB::transaction(function () use ($orderId, $adminId, $adminUsername, $paystackTxnId) {
            $order = Order::lockForUpdate()->find($orderId);

            if (! $order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            if (($order->order_source ?? '') !== 'shop') {
                return ['success' => false, 'message' => 'Not a storefront order'];
            }

            $shopId = $order->shop_id;
            if (! $shopId) {
                return ['success' => false, 'message' => 'Order has no shop'];
            }

            $reference = $order->order_reference ?? '';
            if (empty($reference)) {
                return ['success' => false, 'message' => 'Order has no reference'];
            }

            $noManualVerify = ['verified', 'processing', 'delivered', 'paid', 'cancelled'];
            if (in_array(strtolower($order->status), $noManualVerify)) {
                return ['success' => false, 'message' => 'Order cannot be manually verified in status: '.$order->status];
            }

            $adminLabel = trim($adminUsername);
            if ($adminId && $adminId > 0) {
                $adminLabel = $adminLabel !== '' ? $adminLabel.' (id '.$adminId.')' : 'admin id '.$adminId;
            }
            if ($adminLabel === '') {
                $adminLabel = 'admin';
            }

            $order->update([
                'status' => 'verified',
                'external_transaction_id' => $paystackTxnId,
            ]);

            $this->creditShopProfit($shopId, $orderId);

            return ['success' => true, 'message' => 'Order verified and queued like a successful payment.'];
        });
    }

    private function generateUniqueSlug(string $username): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '', $username));
        if (empty($base)) {
            $base = 'shop';
        }
        $slug = $base.'store';
        $candidate = $slug;
        $n = 0;

        while (Shop::where('shop_slug', $candidate)->exists()) {
            $n++;
            $candidate = $slug.$n;
        }

        return $candidate;
    }

    private function defaultWorkingHoursJson(): string
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $hours = [];
        foreach ($days as $d) {
            $hours[$d] = ['enabled' => true, 'open' => '08:00', 'close' => '18:00'];
        }

        return json_encode($hours);
    }

    private function initializeDefaultPricing(int $shopId, string $userRole): void
    {
        $rows = $this->customPricingService->getAllCustomPricing(['user_role' => $userRole]);
        foreach ($rows as $row) {
            $this->upsertShopPricingRow(
                $shopId,
                $row['package_size'],
                $row['network_type'],
                floatval($row['package_size_gb'] ?? 0),
                floatval($row['cost']),
                floatval($row['cost'])
            );
        }
    }

    private function upsertShopPricingRow(int $shopId, string $packageSize, string $networkType, float $packageSizeGb, float $basePrice, float $sellingPrice): void
    {
        if ($sellingPrice < $basePrice) {
            $sellingPrice = $basePrice;
        }
        $profit = round($sellingPrice - $basePrice, 2);

        ShopPricing::updateOrCreate(
            [
                'shop_id' => $shopId,
                'package_size' => $packageSize,
                'network_type' => $networkType,
            ],
            [
                'package_size_gb' => $packageSizeGb,
                'base_price' => $basePrice,
                'selling_price' => $sellingPrice,
                'profit' => $profit,
            ]
        );
    }
}
