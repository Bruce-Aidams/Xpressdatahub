<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Order;
use App\Models\Shop;
use App\Services\OrderService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with('agent:id,username,phone');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($network = $request->input('network_type')) {
            $query->where('network_type', $network);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('guest_id', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($q2) use ($search) {
                        $q2->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo.' 23:59:59');
        }

        $orders = $query->orderByDesc('created_at')->paginate(25);

        $totalRevenue = Order::whereIn('status', ['completed', 'delivered'])->sum('amount');
        $totalOrders = Order::count();
        $totalAgents = Agent::count();
        $activeShops = Shop::where('is_active', true)->count();

        $stats = [
            'revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_agents' => $totalAgents,
            'active_shops' => $activeShops,
        ];

        $thisWeekRevenue = Order::whereIn('status', ['completed', 'delivered'])->where('created_at', '>=', now()->subWeek())->sum('amount');
        $lastWeekRevenue = Order::whereIn('status', ['completed', 'delivered'])->where('created_at', '>=', now()->subWeeks(2))->where('created_at', '<', now()->subWeek())->sum('amount');
        $revenueChange = $lastWeekRevenue > 0 ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1) : 0;

        $thisWeekOrders = Order::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekOrders = Order::where('created_at', '>=', now()->subWeeks(2))->where('created_at', '<', now()->subWeek())->count();
        $orderChange = $lastWeekOrders > 0 ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1) : 0;

        $thisWeekAgents = Agent::where('created_at', '>=', now()->subWeek())->count();
        $lastWeekAgents = Agent::where('created_at', '>=', now()->subWeeks(2))->where('created_at', '<', now()->subWeek())->count();
        $agentChange = $lastWeekAgents > 0 ? round((($thisWeekAgents - $lastWeekAgents) / $lastWeekAgents) * 100, 1) : 0;

        $totalShops = Shop::count();
        $shopChange = $totalShops > 0 ? round(($activeShops / $totalShops) * 100, 1) : 0;

        // Last 12 hours order counts for the line chart
        $hourlyOrders = collect();
        for ($i = 11; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hourlyOrders->push([
                'label' => $hour->format('H'),
                'count' => Order::where('created_at', '>=', $hour->startOfHour())->where('created_at', '<', $hour->copy()->addHour())->count(),
            ]);
        }
        $maxHourlyVal = $hourlyOrders->pluck('count')->max();
        $maxHourly = $maxHourlyVal > 0 ? $maxHourlyVal : 1;

        return view('admin.orders.index', compact(
            'orders', 'stats', 'revenueChange', 'orderChange', 'agentChange', 'shopChange',
            'hourlyOrders', 'maxHourly'
        ));
    }

    public function show(Order $order)
    {
        $order->load('agent:id,username,phone,email', 'payment', 'statusHistory');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,delivered,failed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->orderService->updateOrderStatus(
                $order->id,
                $request->input('status'),
                $request->input('notes')
            );

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Order status updated successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to update order status.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating order status.');
        }
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|string|in:pending,processing,delivered,failed,cancelled',
        ]);

        $orderIds = $request->input('order_ids');
        $status = $request->input('status');
        $successCount = 0;
        $failCount = 0;

        foreach ($orderIds as $orderId) {
            $result = $this->orderService->updateOrderStatus(
                $orderId,
                $status,
                'Bulk update by admin',
                'admin'
            );

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = ucfirst($status)." status applied to {$successCount} order(s) successfully.";
        if ($failCount > 0) {
            $message .= " {$failCount} order(s) could not be updated.";
        }

        return redirect()->back()->with('success', $message);
    }
}
