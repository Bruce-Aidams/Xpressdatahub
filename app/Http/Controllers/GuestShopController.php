<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopPricing;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use App\Services\PaystackService;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuestShopController extends Controller
{
    public function __construct(
        private ShopService $shopService,
        private OrderService $orderService
    ) {}

    public function show($slug)
    {
        $shop = Shop::with('agent:id,username', 'setting', 'pricing')
            ->where('shop_slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $shop) {
            abort(404, 'Shop not found or not available.');
        }

        $products = $shop->pricing->sortBy('network_type');

        return view('shop.public', compact('shop', 'products'));
    }

    public function order(Request $request, $slug)
    {
        $shop = Shop::with('pricing')
            ->where('shop_slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $shop) {
            abort(404, 'Shop not found or not available.');
        }

        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'product_id' => 'required|integer',
        ]);

        $pricing = ShopPricing::where('shop_id', $shop->id)
            ->where('id', $request->input('product_id'))
            ->first();

        if (! $pricing) {
            return redirect()->back()
                ->with('error', 'Selected package not found.');
        }

        $amount = floatval($pricing->selling_price);
        $reference = 'SHOP-'.strtoupper(Str::random(8)).'-'.time();

        $result = PaystackService::initializeTransaction([
            'email' => 'guest-'.Str::random(6).'@shoporder.com',
            'amount' => (int) round($amount * 100),
            'reference' => $reference,
            'metadata' => [
                'type' => 'shop_order',
                'shop_id' => $shop->id,
                'shop_slug' => $shop->shop_slug,
                'pricing_id' => $pricing->id,
                'phone' => $request->input('phone'),
                'network' => $pricing->network_type,
                'package' => $pricing->package_size,
                'amount' => $amount,
            ],
        ]);

        if (! $result['success']) {
            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to initialize payment. Please try again.');
        }

        $orderResult = $this->orderService->createOrder([
            'agent_id' => null,
            'guest_id' => 'GST-'.strtoupper(Str::random(6)),
            'phone_number' => $request->input('phone'),
            'network_type' => $pricing->network_type,
            'package_size' => $pricing->package_size,
            'amount' => $amount,
            'payment_method' => 'paystack',
            'transaction_id' => $reference,
            'shop_id' => $shop->id,
            'order_source' => 'shop',
            'order_reference' => $reference,
            'base_amount' => $pricing->base_price,
            'paystack_total' => $amount,
        ]);

        return redirect($result['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()->route('shop.order.error')
                ->with('error', 'No payment reference found.');
        }

        $result = PaystackService::verifyTransaction($reference);

        if (! $result['success']) {
            return redirect()->route('shop.order.error')
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        $data = $result['data'];
        $status = $data['status'] ?? '';
        $metadata = $data['metadata'] ?? [];

        $order = Order::where('transaction_id', $reference)->first();

        if (! $order) {
            return redirect()->route('shop.order.error')
                ->with('error', 'Order not found. Please contact support.');
        }

        if (in_array($order->status, ['delivered', 'processing', 'verified'])) {
            return redirect()->route('shop.order.confirm', $order->id)
                ->with('success', 'Payment already verified. Your order is being processed.');
        }

        if ($status === 'success') {
            $order->update([
                'status' => 'processing',
                'status_updated_at' => now(),
            ]);

            // Attempt data delivery
            try {
                $externalService = new ExternalApiService($order->network_type);
                $packageMb = $this->convertPackageToMb($order->package_size);

                $deliveryResult = $externalService->purchaseData(
                    $order->phone_number,
                    $order->network_type,
                    $packageMb,
                    'paystack',
                    (string) $order->id
                );

                if ($deliveryResult['success']) {
                    $order->update([
                        'status' => 'delivered',
                        'external_transaction_id' => $deliveryResult['data']['transaction_id'] ?? null,
                        'external_reference' => $deliveryResult['data']['reference'] ?? null,
                        'api_response_data' => json_encode($deliveryResult['data'] ?? []),
                        'status_updated_at' => now(),
                    ]);

                    // Credit shop profit
                    if ($order->shop_id) {
                        try {
                            $this->shopService->creditShopProfit((int) $order->shop_id, (int) $order->id);
                        } catch (\Exception $e) {
                            Log::error("Shop profit credit error for order #{$order->id}: {$e->getMessage()}");
                        }
                    }
                } else {
                    $order->update([
                        'status' => 'pending',
                        'api_response_data' => json_encode($deliveryResult ?? []),
                        'status_updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Data delivery error for order #{$order->id}: {$e->getMessage()}");
                $order->update([
                    'status' => 'pending',
                    'status_updated_at' => now(),
                ]);
            }

            return redirect()->route('shop.order.confirm', $order->id);
        }

        $order->update([
            'status' => 'cancelled',
            'status_updated_at' => now(),
        ]);

        return redirect()->route('shop.order.error')
            ->with('error', 'Payment was not successful. Your order has been cancelled.');
    }

    public function confirm($orderId)
    {
        $order = Order::find($orderId);

        if (! $order) {
            return redirect()->route('shop.order.error')
                ->with('error', 'Order not found.');
        }

        $shop = $order->shop_id ? Shop::find($order->shop_id) : null;

        return view('shop.confirm', compact('order', 'shop'));
    }

    public function error()
    {
        return view('shop.error');
    }

    private function convertPackageToMb(string $package): int
    {
        $package = strtoupper(trim($package));

        if (str_ends_with($package, 'GB')) {
            return (int) (floatval($package) * 1024);
        }

        if (str_ends_with($package, 'MB')) {
            return (int) floatval($package);
        }

        return (int) floatval($package);
    }
}
