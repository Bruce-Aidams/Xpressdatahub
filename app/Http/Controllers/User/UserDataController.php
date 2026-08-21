<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomPricing;
use App\Models\Order;
use App\Services\BalanceHistoryService;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserDataController extends Controller
{
    public function index()
    {
        $isGuest = session('guest_mode');
        $networks = ['MTN', 'Telecel', 'AirtelTigo'];

        if ($isGuest) {
            $agent = (object) [
                'id' => null,
                'username' => 'Guest',
                'role' => 'guest',
                'balance' => 0,
            ];
            $pricing = CustomPricing::where('is_active', true)
                ->orderBy('network_type')
                ->orderBy('package_size_gb')
                ->get()
                ->groupBy('network_type');
        } else {
            $userId = session('user_id');
            $agent = Agent::find($userId);

            $pricing = CustomPricing::where('is_active', true)
                ->where(function ($q) use ($agent) {
                    $q->where('user_role', 'all')
                        ->orWhere('user_role', $agent->role ?? 'agent');
                })
                ->orderBy('network_type')
                ->orderBy('package_size_gb')
                ->get()
                ->groupBy('network_type');
        }

        // Merge packages: expand 'all'-network packages into every specific network,
        // combining them with any existing specific-network packages.
        $expanded = collect();
        foreach ($networks as $net) {
            $expanded->put($net, collect());
        }
        foreach ($pricing as $network => $packages) {
            if ($network === 'all') {
                // Spread 'all'-network packages to every network bucket
                foreach ($networks as $net) {
                    $expanded->put($net, $expanded->get($net)->merge($packages));
                }
            } else {
                // Add specific-network packages to their bucket
                if (isset($expanded[$network])) {
                    $expanded->put($network, $expanded->get($network)->merge($packages));
                } else {
                    $expanded->put($network, $packages);
                }
            }
        }
        // Remove empty networks so network tabs show N/A correctly
        $pricing = $expanded->filter(fn ($pkgs) => $pkgs->isNotEmpty());

        $momoNumberConfig = \App\Models\PaymentConfig::where('config_key', 'admin_momo_number')->first();
        $momoNameConfig = \App\Models\PaymentConfig::where('config_key', 'admin_momo_name')->first();
        $momoNumber = $momoNumberConfig ? $momoNumberConfig->config_value : 'Not Configured';
        $momoName = $momoNameConfig ? $momoNameConfig->config_value : '';

        return view('user.buy-data.index', compact('agent', 'pricing', 'isGuest', 'momoNumber', 'momoName'));
    }

    public function store(Request $request)
    {
        $isGuest = session('guest_mode');

        $request->validate([
            'network_type' => 'required|string|in:MTN,Telecel,AirtelTigo',
            'package_size' => 'required|string',
            'phone_number' => 'required|string|digits:10',
        ]);

        $networkType = $request->input('network_type');
        $packageSize = $request->input('package_size');
        $phoneNumber = $request->input('phone_number');

        // Match the exact network OR packages set for 'all' networks
        $pricingQuery = CustomPricing::where(function ($q) use ($networkType) {
            $q->where('network_type', $networkType)
                ->orWhere('network_type', 'all');
        })
            ->where('package_size', $packageSize)
            ->where('is_active', true);

        if (! $isGuest) {
            $userId = session('user_id');
            $agent = Agent::find($userId);

            $pricingQuery->where(function ($q) use ($agent) {
                $q->where('user_role', 'all')
                    ->orWhere('user_role', $agent->role ?? 'agent');
            });
        }

        $pricing = $pricingQuery->first();

        if (! $pricing) {
            return redirect()->back()
                ->with('error', 'Selected package not found or unavailable.');
        }

        $amount = floatval($pricing->selling_price);

        $validatedPhone = $this->validateGhanaPhone($phoneNumber);
        if (! $validatedPhone) {
            return redirect()->back()
                ->with('error', 'Invalid phone number. Please enter a valid Ghana phone number (e.g. 0241234567).');
        }

        if (! $isGuest) {
            $userId = session('user_id');
            $agent = Agent::find($userId);
            if ($agent) {
                $cleanedPhone = preg_replace('/\D+/', '', $validatedPhone);
                if ($cleanedPhone === '0500000000' || $cleanedPhone === '233500000000' || $cleanedPhone === '500000000') {
                    if (! empty($agent->phone)) {
                        $validatedPhone = $this->validateGhanaPhone($agent->phone) ?: $agent->phone;
                    }
                }
            }
        }

        if (! $this->phoneMatchesNetwork($validatedPhone, $networkType)) {
            return redirect()->back()
                ->with('error', "Phone number does not belong to {$networkType} network.");
        }

        $reference = ($isGuest ? 'GUEST-' : 'ORD-').strtoupper(Str::random(8)).'-'.time();

        if ($isGuest) {
            $paymentMethod = $request->input('payment_method', 'paystack');
            if ($paymentMethod === 'manual_momo') {
                $request->validate([
                    'sender_name' => 'required|string|max:255',
                ]);
            }

            $guestId = session('guest_id');
            if (! $guestId) {
                $guestId = 'GST-'.strtoupper(Str::random(6));
                session()->put('guest_id', $guestId);
            }

            if ($paymentMethod === 'manual_momo') {
                Order::create([
                    'agent_id' => null,
                    'guest_id' => $guestId,
                    'phone_number' => $validatedPhone,
                    'network_type' => $networkType,
                    'package_size' => $packageSize,
                    'amount' => $amount,
                    'payment_method' => 'manual_momo',
                    'transaction_id' => $reference,
                    'order_source' => 'guest',
                    'order_reference' => $reference,
                    'base_amount' => floatval($pricing->cost),
                    'status' => 'payment_pending',
                    'sender_name' => $request->input('sender_name'),
                ]);

                return redirect()->route('guest.order.success');
            }

            $paystack = app(PaystackService::class);
            $callbackUrl = route('guest.callback');
            $email = 'guest-'.Str::random(6).'@guestorder.com';

            $paystackResult = $paystack->initializeTransaction([
                'email' => $email,
                'amount' => $amount * 100,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'network_type' => $networkType,
                    'package_size' => $packageSize,
                    'phone_number' => $validatedPhone,
                    'order_reference' => $reference,
                    'base_amount' => floatval($pricing->cost),
                ],
            ]);

            if (! $paystackResult['success']) {
                return redirect()->back()
                    ->with('error', 'Failed to initialize payment. Please try again.');
            }

            Order::create([
                'agent_id' => null,
                'guest_id' => $guestId,
                'phone_number' => $validatedPhone,
                'network_type' => $networkType,
                'package_size' => $packageSize,
                'amount' => $amount,
                'payment_method' => 'paystack',
                'transaction_id' => $reference,
                'order_source' => 'guest',
                'order_reference' => $reference,
                'base_amount' => floatval($pricing->cost),
                'status' => 'payment_pending',
            ]);

            $authorizationUrl = $paystackResult['data']['authorization_url'] ?? null;
            if ($authorizationUrl) {
                return redirect($authorizationUrl);
            }

            return redirect()->route('user.buy-data')
                ->with('error', 'Could not get payment link. Please try again.');
        }

        $userId = session('user_id');
        $agent = Agent::find($userId);

        if (floatval($agent->balance) < $amount) {
            return redirect()->back()
                ->with('error', 'Insufficient balance. Please top up your wallet first. Required: GH₵'.number_format($amount, 2));
        }

        DB::beginTransaction();

        try {
            $orderService = app(OrderService::class);
            $result = $orderService->createOrder([
                'agent_id' => $userId,
                'phone_number' => $validatedPhone,
                'network_type' => $networkType,
                'package_size' => $packageSize,
                'amount' => $amount,
                'payment_method' => 'wallet',
                'transaction_id' => $reference,
                'order_source' => 'agent',
                'order_reference' => $reference,
                'base_amount' => floatval($pricing->cost),
            ]);

            if (! $result['success']) {
                DB::rollBack();

                return redirect()->back()
                    ->with('error', 'Failed to create order. Please try again.');
            }

            $newBalance = floatval($agent->balance) - $amount;
            $agent->update(['balance' => $newBalance]);

            BalanceHistoryService::log(
                $userId,
                -$amount,
                'order',
                $result['order']['id'] ?? null,
                null,
                "Data purchase for {$validatedPhone}"
            );

            DB::commit();

            $externalApi = new ExternalApiService($networkType);
            $capacityMb = $externalApi->convertPackageSize($packageSize);

            if ($capacityMb > 0) {
                $apiResult = $externalApi->purchaseData(
                    $validatedPhone,
                    $networkType,
                    $capacityMb,
                    'wallet',
                    (string) ($result['order']['id'] ?? '')
                );

                $orderId = $result['order']['id'] ?? null;
                if ($orderId) {
                    if ($apiResult['success']) {
                        $externalTransactionId = $apiResult['data']['data']['transaction_id']
                            ?? $apiResult['data']['transaction_id']
                            ?? $apiResult['data']['data']['purchaseId']
                            ?? $apiResult['data']['purchaseId']
                            ?? null;
                        $externalReference = $apiResult['data']['data']['transactionReference']
                            ?? $apiResult['data']['transactionReference']
                            ?? null;

                        Order::where('id', $orderId)->update([
                            'status' => 'processing',
                            'external_transaction_id' => $externalTransactionId ? (string) $externalTransactionId : null,
                            'external_reference' => $externalReference,
                            'api_response_data' => json_encode($apiResult['data'] ?? []),
                            'status_updated_at' => now(),
                        ]);
                    } else {
                        $orderService = app(OrderService::class);
                        $orderService->updateOrderStatus(
                            $orderId,
                            'failed',
                            $apiResult['error'] ?? 'API call failed',
                            'system'
                        );
                        Order::where('id', $orderId)->update([
                            'api_response_data' => json_encode(['error' => $apiResult['error'] ?? 'API call failed']),
                        ]);
                    }
                }
            }

            return redirect()->route('user.orders.today')
                ->with('success', 'Order placed successfully! Phone: '.$validatedPhone.', Amount: GH₵'.number_format($amount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()
                ->with('error', 'An error occurred while processing your order. Please try again.');
        }
    }

    private function validateGhanaPhone(string $phone): ?string
    {
        $cleaned = preg_replace('/[\s\-]/', '', trim($phone));

        if (preg_match('/^0[235]\d{8}$/', $cleaned)) {
            return $cleaned;
        }

        if (preg_match('/^233[235]\d{8}$/', $cleaned)) {
            return $cleaned;
        }

        if (preg_match('/^\+233[235]\d{8}$/', $cleaned)) {
            return $cleaned;
        }

        if (preg_match('/^[235]\d{8}$/', $cleaned)) {
            return '0'.$cleaned;
        }

        return null;
    }

    private function phoneMatchesNetwork(string $phone, string $network): bool
    {
        $prefix = substr($phone, 0, 3);

        $networkPrefixes = [
            'MTN' => ['024', '025', '053', '054', '055', '059'],
            'Telecel' => ['020', '050'],
            'AirtelTigo' => ['027', '057', '026', '056', '023'],
        ];

        return in_array($prefix, $networkPrefixes[$network] ?? []);
    }
}
