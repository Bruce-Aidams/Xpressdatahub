<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\ReferralCommission;
use App\Models\ReferralConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateReferralCode(int $userId): string
    {
        $agent = Agent::select('referral_code')->find($userId);
        if ($agent && ! empty($agent->referral_code)) {
            return $agent->referral_code;
        }

        $maxAttempts = 10;
        $attempt = 0;
        $exists = true;

        do {
            $code = 'REF'.strtoupper(Str::random(8));
            $exists = Agent::where('referral_code', $code)->exists();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            $code = 'REF'.str_pad($userId, 8, '0', STR_PAD_LEFT);
        }

        Agent::where('id', $userId)->update(['referral_code' => $code]);

        return $code;
    }

    public function getReferralCode(int $userId): string
    {
        $agent = Agent::select('referral_code')->find($userId);

        if ($agent && ! empty($agent->referral_code)) {
            return $agent->referral_code;
        }

        return $this->generateReferralCode($userId);
    }

    public function trackReferral(int $newUserId, string $referralCode): bool
    {
        if (empty($referralCode)) {
            return false;
        }

        $referrer = Agent::where('referral_code', trim($referralCode))->first();
        if (! $referrer) {
            return false;
        }

        if ($referrer->id === $newUserId) {
            return false;
        }

        $newUser = Agent::find($newUserId);
        if (! $newUser) {
            return false;
        }

        if (! empty($newUser->device_id) && ! empty($referrer->device_id)) {
            if ($newUser->device_id === $referrer->device_id) {
                return false;
            }
        }

        if (! empty($newUser->registration_ip)) {
            if ($newUser->registration_ip === $referrer->registration_ip
                || $newUser->registration_ip === $referrer->last_login_ip) {
                return false;
            }

            $logOverlap = DB::table('user_login_logs')
                ->where('user_id', $referrer->id)
                ->where('ip_address', $newUser->registration_ip)
                ->exists();

            if ($logOverlap) {
                return false;
            }
        }

        return Agent::where('id', $newUserId)
            ->whereNull('referred_by')
            ->update(['referred_by' => $referrer->id]) > 0;
    }

    public function calculateCommission(array $orderData): array
    {
        $orderId = intval($orderData['order_id']);
        $referredUserId = intval($orderData['referred_user_id']);
        $orderAmount = floatval($orderData['order_amount']);
        $orderDate = $orderData['order_date'] ?? date('Y-m-d');
        $triggeredBy = $orderData['triggered_by'] ?? null;

        if ($orderId <= 0 || $referredUserId <= 0 || $orderAmount <= 0) {
            return ['success' => false, 'commission_id' => null, 'message' => 'Invalid inputs'];
        }

        if (! $this->isCommissionEnabled()) {
            return ['success' => false, 'commission_id' => null, 'message' => 'Commission system is disabled'];
        }

        return DB::transaction(function () use ($orderId, $referredUserId, $orderAmount, $orderDate) {
            $existing = ReferralCommission::where('order_id', $orderId)
                ->whereIn('status', ['pending', 'paid'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'success' => false,
                    'commission_id' => $existing->id,
                    'message' => 'Commission already exists',
                ];
            }

            $user = Agent::where('id', $referredUserId)
                ->lockForUpdate()
                ->first();

            if (! $user || ! $user->referred_by) {
                return ['success' => false, 'commission_id' => null, 'message' => 'No referrer found'];
            }

            $referrerId = intval($user->referred_by);

            $commissionPercentage = $this->getCommissionPercentage();
            $commissionAmount = round(($orderAmount * $commissionPercentage) / 100, 2);

            if ($commissionAmount <= 0) {
                return ['success' => false, 'commission_id' => null, 'message' => 'Invalid commission amount'];
            }

            $commission = ReferralCommission::create([
                'referrer_id' => $referrerId,
                'referred_user_id' => $referredUserId,
                'order_id' => $orderId,
                'order_amount' => $orderAmount,
                'commission_percentage' => $commissionPercentage,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
                'commission_date' => $orderDate,
            ]);

            $this->updateDailyStats($referrerId, $orderDate, $orderAmount, $commissionAmount);

            return [
                'success' => true,
                'commission_id' => $commission->id,
                'message' => 'Commission calculated successfully',
            ];
        });
    }

    public function processDailyCommissions(?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');

        if (! $this->isCommissionEnabled()) {
            return ['success' => false, 'message' => 'Commission system is disabled'];
        }

        $pendingCommissions = ReferralCommission::where('commission_date', $date)
            ->where('status', 'pending')
            ->get();

        $processed = 0;
        $totalAmount = 0;

        foreach ($pendingCommissions as $commission) {
            try {
                DB::transaction(function () use ($commission) {
                    Agent::where('id', $commission->referrer_id)
                        ->increment('balance', $commission->commission_amount);

                    BalanceHistoryService::log(
                        $commission->referrer_id,
                        $commission->commission_amount,
                        'commission',
                        $commission->order_id,
                        null,
                        'Referral commission earned'
                    );

                    $commission->update(['status' => 'paid', 'credited_at' => now()]);
                });

                $processed++;
                $totalAmount += $commission->commission_amount;
            } catch (\Exception $e) {
                report($e);
            }
        }

        return [
            'success' => true,
            'processed' => $processed,
            'total_amount' => $totalAmount,
        ];
    }

    public function processMissingCommissions(int $limit = 50): array
    {
        $orders = DB::table('agent_orders')
            ->join('agents', 'agents.id', '=', 'agent_orders.agent_id')
            ->leftJoin('referral_commissions', 'referral_commissions.order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'delivered')
            ->whereNotNull('agents.referred_by')
            ->whereNull('referral_commissions.id')
            ->select(
                'agent_orders.id as order_id',
                'agent_orders.agent_id as referred_user_id',
                'agent_orders.amount as order_amount',
                DB::raw('DATE(agent_orders.created_at) as order_date')
            )
            ->orderBy('agent_orders.created_at')
            ->limit($limit)
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $result = $this->calculateCommission([
                'order_id' => $order->order_id,
                'referred_user_id' => $order->referred_user_id,
                'order_amount' => $order->order_amount,
                'order_date' => $order->order_date,
                'triggered_by' => 'processMissingCommissions',
            ]);

            if ($result['success']) {
                $processed++;
            } elseif ($result['message'] !== 'Commission already exists'
                && $result['message'] !== 'No referrer found') {
                $failed++;
            }
        }

        return [
            'success' => true,
            'processed' => $processed,
            'failed' => $failed,
            'message' => "Processed $processed orders, $failed failed",
        ];
    }

    public function getReferralStats(int $userId): array
    {
        $totalReferrals = Agent::where('referred_by', $userId)->count();

        $pendingCommissions = ReferralCommission::where('referrer_id', $userId)
            ->where('status', 'pending')
            ->count();

        $totalEarned = ReferralCommission::where('referrer_id', $userId)
            ->where('status', 'paid')
            ->sum('commission_amount');

        $pendingAmount = ReferralCommission::where('referrer_id', $userId)
            ->where('status', 'pending')
            ->sum('commission_amount');

        return [
            'total_referrals' => $totalReferrals,
            'pending_commissions' => $pendingCommissions,
            'total_earned' => floatval($totalEarned),
            'pending_amount' => floatval($pendingAmount),
        ];
    }

    public function getCommissionPercentage(): float
    {
        $config = ReferralConfig::where('config_key', 'commission_percentage')->first();

        return $config ? floatval($config->config_value) : 5.0;
    }

    public function isCommissionEnabled(): bool
    {
        $config = ReferralConfig::where('config_key', 'commission_enabled')->first();

        return $config && $config->config_value === '1';
    }

    private function updateDailyStats(int $referrerId, string $date, float $orderAmount, float $commissionAmount): void
    {
        DB::table('referral_stats')
            ->updateOrInsert(
                ['referrer_id' => $referrerId, 'stat_date' => $date],
                [
                    'total_orders' => DB::raw('total_orders + 1'),
                    'delivered_orders' => DB::raw('delivered_orders + 1'),
                    'total_sales' => DB::raw('total_sales + '.$orderAmount),
                    'total_commission' => DB::raw('total_commission + '.$commissionAmount),
                    'updated_at' => now(),
                ]
            );
    }
}
