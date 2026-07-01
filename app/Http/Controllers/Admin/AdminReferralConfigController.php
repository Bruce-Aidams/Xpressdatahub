<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralConfig;
use Illuminate\Http\Request;

class AdminReferralConfigController extends Controller
{
    public function index()
    {
        $configs = ReferralConfig::all();

        return view('admin.config.referral-config', compact('configs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'min_orders_required' => 'required|integer|min:0',
            'max_commission_per_order' => 'nullable|numeric|min:0',
            'is_enabled' => 'nullable|boolean',
        ]);

        try {
            $existing = ReferralConfig::first();

            $data = [
                'commission_percentage' => $request->input('commission_percentage'),
                'min_orders_required' => $request->input('min_orders_required'),
                'max_commission_per_order' => $request->input('max_commission_per_order'),
                'is_enabled' => $request->boolean('is_enabled', false),
                'admin_id' => session('admin_id'),
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                ReferralConfig::create($data);
            }

            return redirect()->back()
                ->with('success', 'Referral configuration updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update referral configuration.');
        }
    }
}
