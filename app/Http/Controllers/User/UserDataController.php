<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomPricing;
use App\Models\Order;
use App\Services\ExternalApiService;
use App\Services\OrderService;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserDataController extends Controller
{
    public function index()
    {
        $isGuest = session('guest_mode');

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

        return view('user.buy-data.index', compact('agent', 'pricing', 'isGuest'));
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

        $pricingQuery = CustomPricing::where('network_type', $networkType)
            ->where('package_size', $packageSize)
            ->where('is_active', true);

        if (!$isGuest) {
            $userId = session('user_id');
            $agent = Agent::find($userId);

            $pricingQuery->where(function ($q) use ($agent) {
                $q->where('user_role', 'all')
                    ->orWhere('user_role', $agent->role ?? 'agent');
            });
        }

        $pricing = $pricingQuery->first();

        if (!$pricing) {
            return redirect()->back()
                ->with('error', 'Selected package not found or unavailable.');
        }

        $amount = floatval($pricing->selling_price);

        $validatedPhone = $this->validateGhanaPhone($phoneNumber);
        if (!$validatedPhone) {
            return redirect()->back()
                ->with('error', 'Invalid phone number. Please enter a valid Ghana phone number (e.g. 0241234567).');
        }

        if (!$this->phoneMatchesNetwork($validatedPhone, $networkType)) {
            return redirect()->back()
                ->with('error', "Phone number does not belong to {$networkType} network.");
        }

        $reference = ($isGuest ? 'GUEST-' : 'ORD-') . strtoupper(Str::random(8)) . '-' . time();

        if ($isGuest) {
            $paystack = app(PaystackService::class);
            $callbackUrl = route('guest.callback');
            $email = 'guest-' . Str::random(6) . '@guestorder.com';

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

            if (!$paystackResult['success']) {
                return redirect()->back()
                    ->with('error', 'Failed to initialize payment. Please try again.');
            }

            $guestId = session('guest_id');
            if (!$guestId) {
                $guestId = 'GST-' . strtoupper(Str::random(6));
                session()->put('guest_id', $guestId);
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
                ->with('error', 'Insufficient balance. Please top up your wallet first. Required: GH₵' . number_format($amount, 2));
        }

        \Illuminate\Support\Facades\DB::beginTransaction();

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

            if (!$result['success']) {
                \Illuminate\Support\Facades\DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Failed to create order. Please try again.');
            }

            $newBalance = floatval($agent->balance) - $amount;
            $agent->update(['balance' => $newBalance]);

            \App\Services\BalanceHistoryService::log(
                $userId,
                -$amount,
                'order',
                $result['order']['id'] ?? null,
                $validatedPhone
            );

            \Illuminate\Support\Facades\DB::commit();

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

                if ($apiResult['success']) {
                    $orderId = $result['order']['id'] ?? null;
                    if ($orderId) {
                        Order::where('id', $orderId)->update([
                            'status' => 'processing',
                            'transaction_id' => $apiResult['data']['data']['transaction_id'] ?? $reference,
                        ]);
                    }
                }
            }

            return redirect()->route('user.orders.today')
                ->with('success', 'Order placed successfully! Phone: ' . $validatedPhone . ', Amount: GH₵' . number_format($amount, 2));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
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
            return '0' . $cleaned;
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
