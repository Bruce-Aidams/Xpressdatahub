<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentConfigService;
use Illuminate\Http\Request;

class AdminPaymentConfigController extends Controller
{
    public function __construct(
        private PaymentConfigService $configService
    ) {}

    public function index()
    {
        $configs = $this->configService->getAllConfig();

        return view('admin.config.payment-config', compact('configs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'payment_phone_number' => 'nullable|string|max:50',
            'payment_name' => 'nullable|string|max:255',
            'whatsapp_contact' => 'nullable|string|max:50',
            'whatsapp_group_link' => 'nullable|string|max:500',
        ]);

        try {
            $fields = $request->only([
                'payment_phone_number', 'payment_name',
                'whatsapp_contact', 'whatsapp_group_link',
            ]);

            foreach ($fields as $key => $value) {
                if ($value !== null) {
                    $this->configService->updateConfig($key, $value);
                }
            }

            return redirect()->back()
                ->with('success', 'Payment configuration updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update payment configuration.');
        }
    }
}
