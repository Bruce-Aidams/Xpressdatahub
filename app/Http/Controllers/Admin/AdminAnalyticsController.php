<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::whereIn('status', ['completed', 'delivered'])->sum('amount');
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('amount');
        $dailyRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('amount');

        $totalOrders = Order::count();
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $failedOrders = Order::where('status', 'failed')->count();

        $totalUsers = Agent::count();
        $activeUsers = Agent::where('status', 'active')->count();
        $newUsersToday = Agent::whereDate('created_at', today())->count();

        $revenueByNetwork = Order::whereIn('status', ['completed', 'delivered'])
            ->select('network_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('network_type')
            ->get();

        $revenueByDay = Order::whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $topUsers = Agent::withCount('orders')
            ->withSum('orders', 'amount')
            ->whereHas('orders', function ($q) {
                $q->whereIn('status', ['completed', 'delivered']);
            })
            ->orderByDesc('orders_sum_amount')
            ->limit(10)
            ->get();

        $totalShops = Shop::count();
        $activeShops = Shop::where('is_active', true)->count();

        $analytics = [
            'today_revenue' => $dailyRevenue,
            'today_orders' => $totalOrders,
            'month_revenue' => $monthlyRevenue,
            'success_rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0,
        ];

        // Last 14 days revenue for chart
        $chartData = collect();
        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $rev = Order::whereDate('created_at', $day)->whereIn('status', ['completed', 'delivered'])->sum('amount');
            $chartData->push(['date' => $day->format('M d'), 'revenue' => $rev]);
        }
        $maxRevVal = $chartData->pluck('revenue')->max();
        $maxRevenue = $maxRevVal > 0 ? $maxRevVal : 1;

        return view('admin.analytics.index', compact(
            'analytics', 'totalRevenue', 'monthlyRevenue', 'dailyRevenue',
            'totalOrders', 'completedOrders', 'pendingOrders', 'failedOrders',
            'totalUsers', 'activeUsers', 'newUsersToday',
            'revenueByNetwork', 'revenueByDay', 'topUsers',
            'totalShops', 'activeShops', 'chartData', 'maxRevenue'
        ));
    }
}
