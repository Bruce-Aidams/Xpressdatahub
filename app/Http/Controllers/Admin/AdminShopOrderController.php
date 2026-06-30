<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use App\Services\ShopService;
use Illuminate\Http\Request;

class AdminShopOrderController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with('shop:id,shop_slug,name', 'agent:id,username')
            ->where('order_source', 'shop');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($shopId = $request->input('shop_id')) {
            $query->where('shop_id', $shopId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('order_reference', 'like', "%{$search}%")
                    ->orWhereHas('shop', function ($q2) use ($search) {
                        $q2->where('shop_slug', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(25);

        $shops = Shop::all();

        return view('admin.shop-orders.index', compact('orders', 'shops'));
    }

    public function verifyOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'paystack_txn_id' => 'nullable|string|max:255',
        ]);

        try {
            $adminId = session('admin_id');
            $adminUsername = session('admin_username', 'admin');

            $result = $this->shopService->adminFinalizeShopOrderPayment(
                $request->input('order_id'),
                $adminId,
                $adminUsername,
                $request->input('paystack_txn_id')
            );

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while verifying the order.');
        }
    }
}
