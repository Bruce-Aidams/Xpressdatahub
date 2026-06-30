<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaystackChargeManager;
use Illuminate\Http\Request;

class AdminPaystackChargeController extends Controller
{
    public function __construct(
        private PaystackChargeManager $chargeManager
    ) {}

    public function index()
    {
        $chargeConfig = $this->chargeManager->getChargeConfig();

        return view('admin.config.paystack-charge', compact('chargeConfig'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'charge_amount' => 'required|numeric|min:0',
            'charge_type' => 'required|string|in:fixed,percentage',
        ]);

        try {
            $result = $this->chargeManager->updateChargeConfig([
                'charge_amount' => $request->input('charge_amount'),
                'charge_type' => $request->input('charge_type'),
                'admin_id' => session('admin_id'),
            ]);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update Paystack charge configuration.');
        }
    }
}
