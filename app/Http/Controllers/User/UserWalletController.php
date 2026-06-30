<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\MinimumTopupConfig;
use App\Models\PaystackTopupCharge;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserWalletController extends Controller
{
    public function topupForm()
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);

        $minConfig = MinimumTopupConfig::where('is_enabled', true)->first();
        $minimumAmount = $minConfig ? floatval($minConfig->minimum_amount) : 10;

        $chargeConfig = PaystackTopupCharge::where('is_active', true)->first();
        $chargeAmount = $chargeConfig ? floatval($chargeConfig->charge_amount) : 0;
        $chargeType = $chargeConfig ? $chargeConfig->charge_type : 'fixed';

        return view('user.wallet.topup', compact('agent', 'minimumAmount', 'chargeAmount', 'chargeType'));
    }

    public function initializeTopup(Request $request)
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);

        $minConfig = MinimumTopupConfig::where('is_enabled', true)->first();
        $minimumAmount = $minConfig ? floatval($minConfig->minimum_amount) : 10;

        $request->validate([
            'amount' => "required|numeric|min:{$minimumAmount}|max:100000",
        ]);

        $amount = floatval($request->input('amount'));

        $chargeConfig = PaystackTopupCharge::where('is_active', true)->first();
        $chargeAmount = 0;
        if ($chargeConfig && $chargeConfig->is_active) {
            if ($chargeConfig->charge_type === 'percentage') {
                $chargeAmount = ($amount * floatval($chargeConfig->charge_amount)) / 100;
            } else {
                $chargeAmount = floatval($chargeConfig->charge_amount);
            }
        }

        $totalAmount = $amount + $chargeAmount;
        $reference = 'TOPUP-' . strtoupper(Str::random(8)) . '-' . time();

        $email = $agent->email ?? 'user@example.com';

        $result = PaystackService::initializeTransaction([
            'email' => $email,
            'amount' => (int) round($totalAmount * 100),
            'reference' => $reference,
            'metadata' => [
                'agent_id' => $userId,
                'type' => 'wallet_topup',
                'amount' => $amount,
                'charge' => $chargeAmount,
            ],
        ]);

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to initialize payment. Please try again.');
        }

        \App\Models\Payment::create([
            'agent_id' => $userId,
            'amount' => $totalAmount,
            'payment_method' => 'paystack',
            'transaction_id' => $reference,
            'paystack_reference' => $reference,
            'status' => 'pending',
        ]);

        return redirect($result['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('user.wallet.topup')
                ->with('error', 'No payment reference found.');
        }

        $result = PaystackService::verifyTransaction($reference);

        if (!$result['success']) {
            return redirect()->route('user.wallet.topup')
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        $data = $result['data'];
        $status = $data['status'] ?? '';
        $metadata = $data['metadata'] ?? [];

        $payment = \App\Models\Payment::where('paystack_reference', $reference)->first();

        if (!$payment) {
            return redirect()->route('user.wallet.topup')
                ->with('error', 'Payment record not found.');
        }

        if ($payment->status === 'verified') {
            return redirect()->route('user.dashboard')
                ->with('success', 'Payment already verified. Balance updated.');
        }

        if ($status === 'success') {
            $agentId = $payment->agent_id;
            $amount = floatval($payment->amount);

            \Illuminate\Support\Facades\DB::transaction(function () use ($payment, $agentId, $amount) {
                $payment->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);

                \Illuminate\Support\Facades\DB::table('agents')
                    ->where('id', $agentId)
                    ->increment('balance', $amount);

                \App\Services\BalanceHistoryService::log(
                    $agentId,
                    $amount,
                    'payment',
                    $payment->id
                );
            });

            return redirect()->route('user.dashboard')
                ->with('success', 'Wallet topped up successfully! GH₵' . number_format($amount, 2) . ' added.');
        }

        $payment->update(['status' => 'failed']);

        return redirect()->route('user.wallet.topup')
            ->with('error', 'Payment was not successful. Please try again.');
    }
}
