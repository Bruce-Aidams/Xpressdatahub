<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\CustomPricing;
use Illuminate\Http\Request;

class BulkOrderController extends Controller
{
    public function store(Request $request)
    {
        $userId = session('user_id');

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

        $added = 0;
        $skipped = 0;

        foreach ($request->orders as $order) {
            $prefix = substr($order['phone_number'], 0, 3);
            if (! in_array($prefix, $networkPrefixes[$order['network_type']] ?? [])) {
                $skipped++;

                continue;
            }

            $pricing = CustomPricing::where('network_type', $order['network_type'])
                ->where('package_size', $order['package_size'])
                ->where('is_active', true)
                ->first();

            if (! $pricing) {
                $skipped++;

                continue;
            }

            $existing = CartItem::where('agent_id', $userId)
                ->where('network_type', $order['network_type'])
                ->where('package_size', $order['package_size'])
                ->where('phone_number', $order['phone_number'])
                ->first();

            if ($existing) {
                $existing->increment('quantity');
            } else {
                CartItem::create([
                    'agent_id' => $userId,
                    'network_type' => $order['network_type'],
                    'package_size' => $order['package_size'],
                    'amount' => $pricing->selling_price,
                    'cost' => $pricing->cost,
                    'phone_number' => $order['phone_number'],
                    'quantity' => 1,
                ]);
            }
            $added++;
        }

        $message = $added.' item(s) added to cart.';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' skipped (unavailable package).';
        }

        return redirect()->route('user.buy-data')
            ->with('success', $message);
    }
}
