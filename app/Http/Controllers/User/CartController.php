<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CartItem;
use App\Models\CustomPricing;
use App\Models\Order;
use App\Services\BalanceHistoryService;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);
        $cartItems = CartItem::where('agent_id', $userId)->orderBy('created_at', 'desc')->get();
        $total = $cartItems->sum(fn($item) => floatval($item->amount) * $item->quantity);

        return view('user.cart.index', compact('agent', 'cartItems', 'total'));
    }

    public function store(Request $request)
    {
        $userId = session('user_id');

        $request->validate([
            'network_type' => 'required|string|in:MTN,Telecel,AirtelTigo',
            'package_size' => 'required|string',
            'phone_number' => 'required|string|digits:10',
        ]);

        $pricing = CustomPricing::where('network_type', $request->network_type)
            ->where('package_size', $request->package_size)
            ->where('is_active', true)
            ->first();

        if (!$pricing) {
            return redirect()->back()->with('error', 'Package not found or unavailable.');
        }

        $agent = Agent::find($userId);

        // Check if same network+package+phone already in cart, increment qty
        $existing = CartItem::where('agent_id', $userId)
            ->where('network_type', $request->network_type)
            ->where('package_size', $request->package_size)
            ->where('phone_number', $request->phone_number)
            ->first();

        if ($existing) {
            $existing->increment('quantity');
        } else {
            CartItem::create([
                'agent_id' => $userId,
                'network_type' => $request->network_type,
                'package_size' => $request->package_size,
                'amount' => $pricing->selling_price,
                'cost' => $pricing->cost,
                'phone_number' => $request->phone_number,
                'quantity' => 1,
            ]);
        }

        return redirect()->route('user.buy-data')
            ->with('success', $request->package_size . ' ' . $request->network_type . ' added to cart.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $userId = session('user_id');

        if ($cartItem->agent_id != $userId) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'phone_number' => 'required|string|digits:10',
        ]);

        $cartItem->update([
            'quantity' => $request->quantity,
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('user.cart.index')
            ->with('success', 'Cart item updated.');
    }

    public function destroy(CartItem $cartItem)
    {
        $userId = session('user_id');

        if ($cartItem->agent_id != $userId) {
            abort(403);
        }

        $cartItem->delete();

        return redirect()->route('user.cart.index')
            ->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $userId = session('user_id');
        CartItem::where('agent_id', $userId)->delete();

        return redirect()->route('user.cart.index')
            ->with('success', 'Cart cleared.');
    }

    public function checkout(Request $request)
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);
        $cartItems = CartItem::where('agent_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum(fn($item) => floatval($item->amount) * $item->quantity);

        if (floatval($agent->balance) < $total) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Insufficient balance. Required: GH₵' . number_format($total, 2) . '. Your balance: GH₵' . number_format($agent->balance, 2));
        }

        DB::beginTransaction();

        try {
            $orderService = app(OrderService::class);
            $createdOrderIds = [];

            foreach ($cartItems as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $reference = 'CART-' . strtoupper(Str::random(8)) . '-' . time();

                    $result = $orderService->createOrder([
                        'agent_id' => $userId,
                        'phone_number' => $item->phone_number,
                        'network_type' => $item->network_type,
                        'package_size' => $item->package_size,
                        'amount' => $item->amount,
                        'payment_method' => 'wallet',
                        'transaction_id' => $reference,
                        'order_source' => 'agent',
                        'order_reference' => $reference,
                        'base_amount' => $item->cost,
                    ]);

                    if (!$result['success']) {
                        DB::rollBack();
                        return redirect()->route('user.cart.index')
                            ->with('error', 'Failed to create order for ' . $item->package_size . ' ' . $item->network_type . '. Please try again.');
                    }

                    $createdOrderIds[] = $result['order']['id'];
                }
            }

            // Deduct total from wallet
            $newBalance = floatval($agent->balance) - $total;
            $agent->update(['balance' => $newBalance]);

            BalanceHistoryService::log(
                $userId, -$total, 'cart_order', null, 'Bulk cart checkout - ' . $cartItems->count() . ' item(s)'
            );

            // Deliver data for each order
            foreach ($createdOrderIds as $orderId) {
                $order = Order::find($orderId);
                if (!$order) continue;

                $externalApi = new ExternalApiService($order->network_type);
                $capacityMb = $externalApi->convertPackageSize($order->package_size);

                if ($capacityMb > 0) {
                    $apiResult = $externalApi->purchaseData(
                        $order->phone_number,
                        $order->network_type,
                        $capacityMb,
                        'wallet',
                        (string) $order->id
                    );

                    if ($apiResult['success']) {
                        $order->update([
                            'status' => 'processing',
                            'transaction_id' => $apiResult['data']['data']['transaction_id'] ?? $order->transaction_id,
                        ]);
                    }
                }
            }

            // Clear cart
            $cartItems->each->delete();

            DB::commit();

            return redirect()->route('user.orders.today')
                ->with('success', count($createdOrderIds) . ' order(s) placed successfully! Total: GH₵' . number_format($total, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('user.cart.index')
                ->with('error', 'An error occurred while processing your orders. Please try again.');
        }
    }
}
