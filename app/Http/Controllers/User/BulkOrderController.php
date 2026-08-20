<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomPricing;
use App\Models\Order;
use App\Services\BalanceHistoryService;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkOrderController extends Controller
{
    public function store(Request $request)
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);

        if (!$agent) {
            abort(403);
        }

        $request->validate([
            'orders' => 'required|array|min:1|max:50',
            'orders.*.network_type' => 'required|string|in:MTN,Telecel,AirtelTigo',
            'orders.*.package_size' => 'required|string',
            'orders.*.phone_number' => 'required|string|digits:10',
        ]);

        $networkPrefixes = [
            'MTN' => ['024', '025', '053', '054', '055', '059'],
            'Telecel' => ['020', '050'],
            'AirtelTigo' => ['027', '057', '026', '056', '023'],
        ];

        $validOrders = [];
        $totalAmount = 0;
        $skipped = 0;

        foreach ($request->orders as $order) {
            $prefix = substr($order['phone_number'], 0, 3);
            if (!in_array($prefix, $networkPrefixes[$order['network_type']] ?? [])) {
                $skipped++;
                continue;
            }

            $pricing = CustomPricing::where('network_type', $order['network_type'])
                ->where('package_size', $order['package_size'])
                ->where('is_active', true)
                ->where(function ($q) use ($agent) {
                    $q->where('user_role', 'all')
                        ->orWhere('user_role', $agent->role ?? 'agent');
                })
                ->first();

            if (!$pricing) {
                $skipped++;
                continue;
            }

            $validOrders[] = [
                'network_type' => $order['network_type'],
                'package_size' => $order['package_size'],
                'phone_number' => $order['phone_number'],
                'amount' => floatval($pricing->selling_price),
                'cost' => floatval($pricing->cost),
            ];

            $totalAmount += floatval($pricing->selling_price);
        }

        if (empty($validOrders)) {
            return redirect()->route('user.buy-data')
                ->with('error', 'No valid orders found. ' . $skipped . ' skipped.');
        }

        if (floatval($agent->balance) < $totalAmount) {
            return redirect()->route('user.buy-data')
                ->with('error', 'Insufficient balance for bulk order. Required: GH₵' . number_format($totalAmount, 2) . '. Your balance: GH₵' . number_format($agent->balance, 2));
        }

        DB::beginTransaction();

        try {
            $orderService = app(OrderService::class);
            $createdOrderIds = [];

            foreach ($validOrders as $item) {
                $reference = 'BULK-' . strtoupper(Str::random(8)) . '-' . time();

                $result = $orderService->createOrder([
                    'agent_id' => $userId,
                    'phone_number' => $item['phone_number'],
                    'network_type' => $item['network_type'],
                    'package_size' => $item['package_size'],
                    'amount' => $item['amount'],
                    'payment_method' => 'wallet',
                    'transaction_id' => $reference,
                    'order_source' => 'agent',
                    'order_reference' => $reference,
                    'base_amount' => $item['cost'],
                ]);

                if (!$result['success']) {
                    DB::rollBack();
                    return redirect()->route('user.buy-data')
                        ->with('error', 'Failed to create order for ' . $item['package_size'] . ' ' . $item['network_type'] . '. Please try again.');
                }

                $createdOrderIds[] = $result['order']['id'];
            }

            // Deduct total from wallet
            $newBalance = floatval($agent->balance) - $totalAmount;
            $agent->update(['balance' => $newBalance]);

            BalanceHistoryService::log(
                $userId, -$totalAmount, 'bulk_order', null, null, 'Bulk checkout - ' . count($createdOrderIds) . ' item(s)'
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
                        (string)$order->id
                    );

                    if ($apiResult['success']) {
                        $externalTransactionId = $apiResult['data']['data']['transaction_id']
                            ?? $apiResult['data']['transaction_id']
                            ?? $apiResult['data']['data']['purchaseId']
                            ?? $apiResult['data']['purchaseId']
                            ?? null;
                        $externalReference = $apiResult['data']['data']['transactionReference']
                            ?? $apiResult['data']['transactionReference']
                            ?? null;

                        $order->update([
                            'status' => 'processing',
                            'external_transaction_id' => $externalTransactionId ? (string)$externalTransactionId : null,
                            'external_reference' => $externalReference,
                            'api_response_data' => json_encode($apiResult['data'] ?? []),
                            'status_updated_at' => now(),
                        ]);
                    } else {
                        $order->update([
                            'status' => 'failed',
                            'api_response_data' => json_encode(['error' => $apiResult['error'] ?? 'API call failed']),
                            'status_updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            $msg = count($createdOrderIds) . ' bulk order(s) placed successfully! Total: GH₵' . number_format($totalAmount, 2);
            if ($skipped > 0) $msg .= " ({$skipped} skipped)";

            return redirect()->route('user.orders.today')
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return redirect()->route('user.buy-data')
                ->with('error', 'An error occurred while processing your bulk orders. Please try again.');
        }
    }
}
