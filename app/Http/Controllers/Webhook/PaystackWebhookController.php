<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\BalanceHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $event = $payload['event'] ?? '';

            if ($event !== 'charge.success') {
                return response()->json(['status' => 'ignored']);
            }

            $data = $payload['data'] ?? [];
            $reference = $data['reference'] ?? '';
            $amount = $data['amount'] ?? 0;
            $status = $data['status'] ?? '';
            $gatewayResponse = $data['gateway_response'] ?? '';
            $paidAt = $data['paid_at'] ?? null;
            $metadata = $data['metadata'] ?? [];

            if (empty($reference)) {
                return response()->json(['status' => 'error', 'message' => 'Missing reference'], 400);
            }

            $payment = Payment::where('paystack_reference', $reference)->first();

            if (! $payment) {
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            if ($payment->status === 'verified') {
                return response()->json(['status' => 'already_verified']);
            }

            DB::transaction(function () use ($payment, $status, $paidAt) {
                $payment->update([
                    'status' => $status === 'success' ? 'verified' : 'failed',
                    'verified_at' => $paidAt ? now() : now(),
                ]);

                if ($status === 'success') {
                    $agentId = $payment->agent_id;
                    $amount = floatval($payment->amount);

                    DB::table('agents')
                        ->where('id', $agentId)
                        ->increment('balance', $amount);

                    BalanceHistoryService::log(
                        $agentId,
                        $amount,
                        'payment',
                        $payment->id
                    );
                }
            });

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            report($e);

            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 500);
        }
    }
}
