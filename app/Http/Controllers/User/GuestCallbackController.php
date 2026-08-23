<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class GuestCallbackController extends Controller
{
    public function handleCallback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (! $reference) {
            return redirect()->route('login')
                ->with('error', 'Invalid payment callback.');
        }

        $paystack = app(PaystackService::class);
        $result = $paystack->verifyTransaction($reference);

        $order = Order::where('order_reference', $reference)
            ->orWhere('transaction_id', $reference)
            ->first();

        if (! $order) {
            return redirect()->route('login')
                ->with('error', 'Order not found for this payment.');
        }

        if ($result['success'] && isset($result['data']['status']) && $result['data']['status'] === 'success') {
            $externalTransactionId = $result['data']['id'] ?? null;

            $order->update([
                'status' => 'processing',
                'transaction_id' => $externalTransactionId ?? $reference,
            ]);

            $externalApi = new ExternalApiService($order->network_type);
            $capacityMb = $externalApi->convertPackageSize($order->package_size);

            if ($capacityMb > 0) {
                $apiResult = $externalApi->purchaseData(
                    $order->phone_number,
                    $order->network_type,
                    $capacityMb,
                    'paystack',
                    (string) $order->id
                );

                if ($apiResult['success']) {
                    $order->update([
                        'external_transaction_id' => $apiResult['data']['data']['transaction_id'] ?? null,
                    ]);
                    $orderService = app(OrderService::class);
                    $orderService->updateOrderStatus($order->id, 'processing', 'API call successful, processing', 'system');

                    return redirect()->route('guest.order.success')
                        ->with('success', 'Payment successful and data delivery initiated! Your order is being processed.')
                        ->with('order_reference', $reference);
                } else {
                    $orderService = app(OrderService::class);
                    $orderService->updateOrderStatus($order->id, 'failed', $apiResult['error'] ?? 'API call failed', 'system');

                    return redirect()->route('guest.order.success')
                        ->with('success', 'Payment successful! However, there was a delay processing your data order. Our support team has been notified.')
                        ->with('order_reference', $reference);
                }
            }

            return redirect()->route('guest.order.success')
                ->with('success', 'Payment successful! Your order has been received.')
                ->with('order_reference', $reference);
        }

        $orderService = app(OrderService::class);
        $orderService->updateOrderStatus($order->id, 'failed', 'Payment verification failed', 'paystack');

        return redirect()->route('guest.order.success')
            ->with('error', 'Payment verification failed. Please contact support if you were charged.');
    }
}
