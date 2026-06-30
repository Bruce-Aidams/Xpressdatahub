<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class UserOrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('user_id');
        $query = Order::where('agent_id', $userId);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($network = $request->input('network_type')) {
            $query->where('network_type', $network);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $orders = $query->orderByDesc('created_at')->paginate(25);

        return view('user.orders.index', compact('orders'));
    }

    public function todayOrders(Request $request)
    {
        $userId = session('user_id');

        $orders = Order::where('agent_id', $userId)
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get();

        $todayTotal = $orders->sum('amount');
        $todayCompleted = $orders->whereIn('status', ['completed', 'delivered'])->count();
        $todayPending = $orders->where('status', 'pending')->count();

        return view('user.orders.today', compact('orders', 'todayTotal', 'todayCompleted', 'todayPending'));
    }
}
