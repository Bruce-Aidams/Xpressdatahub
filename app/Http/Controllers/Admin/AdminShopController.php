<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopPricing;
use Illuminate\Http\Request;

class AdminShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with('agent:id,username,role')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.shops.index', compact('shops'));
    }

    public function show(Shop $shop)
    {
        $shop->load('agent:id,username,role', 'setting', 'pricing', 'earnings', 'withdrawals');

        $totalEarnings = $shop->earnings()->where('status', 'credited')->sum('profit');
        $pendingEarnings = $shop->earnings()->where('status', 'pending')->sum('profit');
        $totalWithdrawn = $shop->withdrawals()->whereIn('status', ['completed', 'delivered'])->sum('amount');

        return view('admin.shops.show', compact('shop', 'totalEarnings', 'pendingEarnings', 'totalWithdrawn'));
    }

    public function updateStatus(Request $request, Shop $shop)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $shop->update([
                'is_active' => $request->boolean('is_active'),
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Shop status updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update shop status.');
        }
    }

    public function destroy(Shop $shop)
    {
        try {
            $shop->delete();

            return redirect()->route('admin.shops.index')
                ->with('success', 'Shop deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete shop.');
        }
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'shop_ids' => 'required|array|min:1',
            'shop_ids.*' => 'integer|exists:shops,id',
            'is_active' => 'required|boolean',
        ]);

        $shopIds = $request->input('shop_ids');
        $isActive = $request->boolean('is_active');

        Shop::whereIn('id', $shopIds)->update([
            'is_active' => $isActive,
            'updated_at' => now(),
        ]);

        $status = $isActive ? 'activated' : 'deactivated';
        $count = count($shopIds);

        return redirect()->back()
            ->with('success', "{$count} shop(s) {$status} successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'shop_ids' => 'required|array|min:1',
            'shop_ids.*' => 'integer|exists:shops,id',
        ]);

        $shopIds = $request->input('shop_ids');
        $deleted = Shop::whereIn('id', $shopIds)->delete();

        return redirect()->back()
            ->with('success', "{$deleted} shop(s) deleted successfully.");
    }

    public function updatePricing(Request $request, ShopPricing $shopPricing)
    {
        $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        try {
            $sellingPrice = (float) $request->input('selling_price');
            $basePrice = (float) $shopPricing->base_price;

            if ($sellingPrice < $basePrice) {
                return redirect()->back()
                    ->with('error', 'Selling price cannot be less than base price (GH₵'.number_format($basePrice, 2).').');
            }

            $profit = round($sellingPrice - $basePrice, 2);

            $shopPricing->update([
                'selling_price' => $sellingPrice,
                'profit' => $profit,
            ]);

            return redirect()->back()
                ->with('success', 'Pricing updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update pricing.');
        }
    }
}
