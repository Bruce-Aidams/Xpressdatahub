<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\BalanceHistory;
use App\Models\Order;
use App\Services\BannerNotificationService;
use App\Services\ShopService;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function index()
    {
        if (session('guest_mode')) {
            $guestId = session('guest_id');

            $orders = Order::where('guest_id', $guestId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $totalOrders = $orders->count();
            $completedOrders = $orders->whereIn('status', ['completed', 'delivered'])->count();
            $pendingOrders = $orders->whereIn('status', ['pending', 'processing', 'payment_pending'])->count();
            $totalSpent = $orders->whereIn('status', ['completed', 'delivered'])->sum('amount');

            return view('user.guest-dashboard', compact(
                'guestId', 'orders', 'totalOrders', 'completedOrders', 'pendingOrders', 'totalSpent'
            ));
        }

        $userId = session('user_id');
        $agent = Agent::find($userId);

        $totalOrders = Order::where('agent_id', $userId)->count();
        $pendingOrders = Order::where('agent_id', $userId)->where('status', 'pending')->count();
        $completedOrders = Order::where('agent_id', $userId)->whereIn('status', ['completed', 'delivered'])->count();
        $totalSpent = Order::where('agent_id', $userId)->whereIn('status', ['completed', 'delivered'])->sum('amount');
        $todayOrders = Order::where('agent_id', $userId)->whereDate('created_at', today())->count();
        $todaySpent = Order::where('agent_id', $userId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('amount');

        $recentOrders = Order::where('agent_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentActivity = BalanceHistory::where('agent_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $shopService = app(ShopService::class);
        $shop = $userId ? $shopService->getShopByUserId($userId) : null;
        $shopEarnings = null;
        if ($shop) {
            $shopEarnings = $shopService->getShopEarningsSummary($shop['id']);
        }

        $referralCount = Agent::where('referred_by', $userId)->count();
        $referralEarnings = DB::table('referral_commissions')
            ->where('referrer_id', $userId)
            ->sum('commission_amount');

        $thisWeekOrders = Order::where('agent_id', $userId)
            ->where('created_at', '>=', now()->subWeek())->count();
        $lastWeekOrders = Order::where('agent_id', $userId)
            ->where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())->count();
        $orderChange = $lastWeekOrders > 0
            ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1)
            : 0;

        $thisWeekSpent = Order::where('agent_id', $userId)
            ->whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subWeek())->sum('amount');
        $lastWeekSpent = Order::where('agent_id', $userId)
            ->whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())->sum('amount');
        $spendChange = $lastWeekSpent > 0
            ? round((($thisWeekSpent - $lastWeekSpent) / $lastWeekSpent) * 100, 1)
            : 0;

        $weeklyOrders = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $weeklyOrders->push([
                'day' => $day->format('D'),
                'count' => Order::where('agent_id', $userId)->whereDate('created_at', $day)->count(),
            ]);
        }
        $maxWeekly = max($weeklyOrders->pluck('count')->max(), 1);

        $networkStats = Order::select('network_type', DB::raw('count(*) as total'))
            ->where('agent_id', $userId)
            ->whereNotNull('network_type')
            ->groupBy('network_type')
            ->orderByDesc('total')
            ->get();
        $totalNetworkOrders = max($networkStats->sum('total'), 1);

        $topPackages = Order::select('network_type', 'package_size', DB::raw('count(*) as total'))
            ->where('agent_id', $userId)
            ->whereNotNull('package_size')
            ->groupBy('network_type', 'package_size')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $ordersByStatus = Order::where('agent_id', $userId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $hourlyOrders = collect();
        for ($h = 0; $h < 24; $h++) {
            $hourlyOrders->push([
                'label' => str_pad($h, 2, '0', STR_PAD_LEFT),
                'count' => Order::where('agent_id', $userId)
                    ->whereDate('created_at', today())
                    ->whereHour('created_at', $h)
                    ->count(),
            ]);
        }
        $maxHourly = max($hourlyOrders->pluck('count')->max(), 1);

        $stats = [
            'balance' => $agent->balance ?? 0,
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'today_orders' => $todayOrders,
            'today_spent' => $todaySpent,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'referral_count' => $referralCount,
            'referral_earnings' => $referralEarnings,
        ];

        $activeBanner = app(BannerNotificationService::class)->getActiveBanner();

        return view('user.dashboard', compact(
            'agent', 'totalOrders', 'pendingOrders', 'completedOrders',
            'totalSpent', 'todayOrders', 'todaySpent',
            'recentOrders', 'recentActivity', 'shop', 'shopEarnings',
            'referralCount', 'referralEarnings',
            'orderChange', 'spendChange',
            'weeklyOrders', 'maxWeekly',
            'networkStats', 'totalNetworkOrders', 'topPackages',
            'ordersByStatus', 'hourlyOrders', 'maxHourly', 'stats',
            'activeBanner'
        ));
    }
}
