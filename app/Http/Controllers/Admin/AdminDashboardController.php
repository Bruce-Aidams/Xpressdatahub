<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\ShopWithdrawal;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = Agent::count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->count();
        $totalRevenue = Order::whereIn('status', ['completed', 'delivered'])->sum('amount');
        $totalShops = Shop::count();
        $activeShops = Shop::where('is_active', true)->count();
        $pendingWithdrawals = ShopWithdrawal::where('status', 'pending')->count();

        $recentOrders = Order::with('agent:id,username')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentUsers = Agent::orderByDesc('created_at')
            ->limit(10)
            ->get();

        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('amount');

        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('amount');

        $stats = [
            'revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_agents' => $totalUsers,
            'active_shops' => $activeShops,
        ];

        // Last 7 days order counts for bar chart
        $weeklyOrders = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $weeklyOrders->push([
                'day' => $day->format('D'),
                'count' => Order::whereDate('created_at', $day)->count(),
            ]);
        }
        $maxWeekly = max($weeklyOrders->pluck('count')->max(), 1);

        // Network breakdown
        $networkStats = Order::select('network_type', DB::raw('count(*) as total'))
            ->whereNotNull('network_type')
            ->groupBy('network_type')
            ->orderByDesc('total')
            ->get();
        $totalNetworkOrders = max($networkStats->sum('total'), 1);

        // Top packages
        $topPackages = Order::select('network_type', 'package_size', DB::raw('count(*) as total'))
            ->whereNotNull('package_size')
            ->groupBy('network_type', 'package_size')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Previous period comparisons (last 7 days vs 7 days before)
        $thisWeekRevenue = Order::whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subWeek())
            ->sum('amount');
        $lastWeekRevenue = Order::whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())
            ->sum('amount');
        $revenueChange = $lastWeekRevenue > 0
            ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1)
            : 0;

        $thisWeekOrders = Order::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekOrders = Order::where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())->count();
        $orderChange = $lastWeekOrders > 0
            ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1)
            : 0;

        $thisWeekAgents = Agent::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekAgents = Agent::where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())->count();
        $agentChange = $lastWeekAgents > 0
            ? round((($thisWeekAgents - $lastWeekAgents) / $lastWeekAgents) * 100, 1)
            : 0;

        $shopChange = $totalShops > 0 ? round(($activeShops / $totalShops) * 100, 1) : 0;

        return view('admin.dashboard', compact(
            'totalUsers', 'totalOrders', 'pendingOrders', 'completedOrders',
            'totalRevenue', 'totalShops', 'activeShops', 'pendingWithdrawals',
            'recentOrders', 'recentUsers', 'ordersByStatus', 'todayOrders',
            'todayRevenue', 'monthlyRevenue', 'stats', 'weeklyOrders', 'maxWeekly',
            'networkStats', 'totalNetworkOrders', 'topPackages',
            'revenueChange', 'orderChange', 'agentChange', 'shopChange'
        ));
    }
}
