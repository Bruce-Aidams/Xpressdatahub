<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\BalanceHistoryService;
use Illuminate\Support\Facades\DB;

class AdminManualTopupController extends Controller
{
    public function index()
    {
        $topups = Payment::with('agent')
            ->where('payment_method', 'manual_momo')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.topups.manual', compact('topups'));
    }

    public function approve(Payment $payment)
    {
        if ($payment->payment_method !== 'manual_momo' || $payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Invalid payment or already processed.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            DB::table('agents')
                ->where('id', $payment->agent_id)
                ->increment('balance', $payment->amount);

            BalanceHistoryService::log(
                $payment->agent_id,
                floatval($payment->amount),
                'payment',
                $payment->id
            );
        });

        return redirect()->back()->with('success', 'Manual top-up approved and balance added.');
    }

    public function reject(Payment $payment)
    {
        if ($payment->payment_method !== 'manual_momo' || $payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Invalid payment or already processed.');
        }

        $payment->update([
            'status' => 'failed',
        ]);

        return redirect()->back()->with('success', 'Manual top-up rejected.');
    }
}
