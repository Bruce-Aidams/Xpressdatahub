<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomPricing;
use App\Models\Order;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderApiController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ExternalApiService $externalApiService
    ) {}

    private function getAgent(Request $request)
    {
        return $request->attributes->get('api_key')->agent ?? null;
    }

    private function getApiKey(Request $request)
    {
        return $request->attributes->get('api_key');
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'network_type' => 'required|string|in:MTN,Telecel,AirtelTigo',
            'package_size' => 'required|string',
        ]);

        $agent = $this->getAgent($request);
        if (!$agent || ($agent->status ?? 'active') !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active.',
            ], 403);
        }

        $phone = $this->externalApiService->validatePhoneNumber($request->input('phone_number'));
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Ghana phone number.',
            ], 422);
        }

        $networkType = $request->input('network_type');
        $packageSize = $request->input('package_size');

        $pricing = CustomPricing::where('network_type', $networkType)
            ->where('package_size', $packageSize)
            ->where('is_active', true)
            ->first();

        if (!$pricing) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found for this network.',
            ], 422);
        }

        $amount = floatval($pricing->selling_price);
        $baseAmount = floatval($pricing->cost);

        if ($agent->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
            ], 402);
        }

        $orderReference = 'ORD-' . strtoupper(substr(uniqid(), -8)) . '-' . rand(1000, 9999);

        try {
            DB::beginTransaction();

            $newBalance = round($agent->balance - $amount, 2);
            $agent->update(['balance' => $newBalance]);

            $result = $this->orderService->createOrder([
                'agent_id' => $agent->id,
                'phone_number' => $phone,
                'network_type' => $networkType,
                'package_size' => $packageSize,
                'amount' => $amount,
                'base_amount' => $baseAmount,
                'paystack_total' => $amount,
                'payment_method' => 'wallet',
                'order_source' => 'api',
                'order_reference' => $orderReference,
            ]);

            if (!$result['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to create order.',
                ], 500);
            }

            $orderId = $result['order']['id'];
            $capacityMb = $this->externalApiService->convertPackageSize($packageSize);

            $apiResult = $this->externalApiService->purchaseData(
                $phone,
                $networkType,
                $capacityMb,
                'wallet',
                $orderId
            );

            $order = Order::find($orderId);

            if ($apiResult['success']) {
                $externalTransactionId = $apiResult['data']['data']['purchaseId']
                    ?? $apiResult['data']['purchaseId']
                    ?? null;
                $externalReference = $apiResult['data']['data']['transactionReference']
                    ?? $apiResult['data']['transactionReference']
                    ?? null;
                $apiResponseData = json_encode($apiResult['data']);

                $order->update([
                    'status' => 'delivered',
                    'external_transaction_id' => $externalTransactionId ? (string) $externalTransactionId : null,
                    'external_reference' => $externalReference,
                    'api_response_data' => $apiResponseData,
                    'status_updated_at' => now(),
                ]);

                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'old_status' => 'pending',
                    'new_status' => 'delivered',
                    'notes' => 'Order submitted to external API successfully',
                    'changed_by' => 'system',
                    'created_at' => now(),
                ]);

                DB::commit();

                $apiKey = $this->getApiKey($request);
                $apiKey->increment('usage_count');

                return response()->json([
                    'success' => true,
                    'order_id' => $orderId,
                    'order_reference' => $orderReference,
                    'network_type' => $networkType,
                    'package_size' => $packageSize,
                    'phone_number' => $phone,
                    'amount' => $amount,
                    'new_balance' => $newBalance,
                    'status' => 'delivered',
                    'external_transaction_id' => $externalTransactionId,
                    'message' => 'Order submitted and delivered successfully',
                ]);
            } else {
                $apiResponseData = json_encode(['error' => $apiResult['error']]);

                $order->update([
                    'status' => 'failed',
                    'api_response_data' => $apiResponseData,
                    'status_updated_at' => now(),
                ]);

                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'old_status' => 'pending',
                    'new_status' => 'failed',
                    'notes' => 'API call failed: ' . ($apiResult['error'] ?? 'Unknown error'),
                    'created_at' => now(),
                ]);

                DB::commit();

                $apiKey = $this->getApiKey($request);
                $apiKey->increment('usage_count');

                return response()->json([
                    'success' => false,
                    'order_id' => $orderId,
                    'status' => 'failed',
                    'error' => $apiResult['error'] ?? 'API call failed',
                    'message' => 'Order created but failed to submit to external API',
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function listOrders(Request $request)
    {
        $request->validate([
            'status' => 'nullable|string|in:pending,processing,delivered,failed,cancelled',
            'network_type' => 'nullable|string|in:MTN,Telecel,AirtelTigo',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        $agent = $this->getAgent($request);
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $orders = $this->orderService->getOrdersByAgent($agent->id, [
            'status' => $request->input('status'),
            'network_type' => $request->input('network_type'),
            'limit' => $request->input('limit', 50),
            'offset' => $request->input('offset', 0),
        ]);

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'total' => count($orders),
        ]);
    }

    public function showOrder(Request $request, int $orderId)
    {
        $agent = $this->getAgent($request);
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('agent_id', $agent->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'phone_number' => $order->phone_number,
                'network_type' => $order->network_type,
                'package_size' => $order->package_size,
                'amount' => $order->amount,
                'payment_method' => $order->payment_method,
                'order_reference' => $order->order_reference,
                'order_source' => $order->order_source,
                'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function checkOrderStatus(Request $request, string $externalTransactionId)
    {
        $agent = $this->getAgent($request);
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $order = Order::where('order_reference', $externalTransactionId)
            ->where('agent_id', $agent->id)
            ->first();

        if (!$order) {
            $order = Order::where('transaction_id', $externalTransactionId)
                ->where('agent_id', $agent->id)
                ->first();
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'phone_number' => $order->phone_number,
                'network_type' => $order->network_type,
                'package_size' => $order->package_size,
                'amount' => $order->amount,
                'order_reference' => $order->order_reference,
                'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
