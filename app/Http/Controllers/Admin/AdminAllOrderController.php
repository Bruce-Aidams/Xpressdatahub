<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminAllOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('agent:id,username,phone');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($network = $request->input('network_type')) {
            $query->where('network_type', $network);
        }

        if ($source = $request->input('order_source')) {
            $query->where('order_source', $source);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('order_reference', 'like', "%{$search}%")
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

        if ($minAmount = $request->input('min_amount')) {
            $query->where('amount', '>=', $minAmount);
        }

        if ($maxAmount = $request->input('max_amount')) {
            $query->where('amount', '<=', $maxAmount);
        }

        $orders = $query->orderByDesc('created_at')->paginate(25);

        $totalAmount = (clone $query)->sum('amount');
        $totalCount = (clone $query)->count();

        return view('admin.orders.all', compact('orders', 'totalAmount', 'totalCount'));
    }
}
