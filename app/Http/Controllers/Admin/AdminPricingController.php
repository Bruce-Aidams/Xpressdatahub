<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPricing;
use App\Services\CustomPricingService;
use Illuminate\Http\Request;

class AdminPricingController extends Controller
{
    public function __construct(
        private CustomPricingService $pricingService
    ) {}

    public function index()
    {
        $pricing = CustomPricing::orderBy('package_size_gb')
            ->paginate(20);

        return view('admin.pricing.index', ['pricingRules' => $pricing]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'package_size' => 'required|string|max:50',
            'network_type' => 'required|string|max:50',
            'cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'user_role' => 'required|string|in:agent,super_agent,dealers,all',
        ]);

        try {
            $result = $this->pricingService->setCustomPricing([
                'package_size' => $request->input('package_size'),
                'network_type' => $request->input('network_type'),
                'cost' => $request->input('cost'),
                'selling_price' => $request->input('selling_price'),
                'user_role' => $request->input('user_role'),
                'created_by' => session('admin_id'),
            ]);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Pricing '.($result['action'] ?? 'saved').' successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to save pricing.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while saving pricing.');
        }
    }

    public function update(Request $request, CustomPricing $customPricing)
    {
        $request->validate([
            'cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $customPricing->update([
                'cost' => $request->input('cost'),
                'selling_price' => $request->input('selling_price', $customPricing->selling_price),
                'is_active' => $request->boolean('is_active', $customPricing->is_active),
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Pricing updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update pricing.');
        }
    }

    public function toggle(CustomPricing $customPricing)
    {
        try {
            $customPricing->update([
                'is_active' => ! $customPricing->is_active,
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Pricing status toggled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle status.');
        }
    }

    public function destroy(CustomPricing $customPricing)
    {
        try {
            $customPricing->delete();

            return redirect()->back()
                ->with('success', 'Pricing deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete pricing.');
        }
    }

    public function bulkToggle(Request $request)
    {
        $request->validate([
            'pricing_ids' => 'required|array|min:1',
            'pricing_ids.*' => 'integer|exists:custom_pricing,id',
        ]);

        $pricingIds = $request->input('pricing_ids');
        $activated = 0;
        $deactivated = 0;

        foreach ($pricingIds as $id) {
            $pricing = CustomPricing::find($id);
            if ($pricing) {
                $pricing->update([
                    'is_active' => ! $pricing->is_active,
                    'updated_at' => now(),
                ]);
                if ($pricing->is_active) {
                    $activated++;
                } else {
                    $deactivated++;
                }
            }
        }

        $message = "{$activated} rule(s) activated, {$deactivated} rule(s) deactivated.";

        return redirect()->back()->with('success', $message);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'pricing_ids' => 'required|array|min:1',
            'pricing_ids.*' => 'integer|exists:custom_pricing,id',
        ]);

        $pricingIds = $request->input('pricing_ids');
        $deleted = CustomPricing::whereIn('id', $pricingIds)->delete();

        return redirect()->back()
            ->with('success', "{$deleted} pricing rule(s) deleted successfully.");
    }
}
