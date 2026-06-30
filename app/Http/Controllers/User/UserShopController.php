<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CustomPricing;
use App\Services\ShopService;
use Illuminate\Http\Request;

class UserShopController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index()
    {
        $userId = session('user_id');
        $shopArray = $this->shopService->getShopByUserId($userId);

        if (!$shopArray) {
            $agent = \App\Models\Agent::find($userId);
            $shopArray = $this->shopService->createShopForUser($userId, $agent->username ?? 'user');
        }

        // Load the Eloquent model so the Blade template can use object notation
        $shop = \App\Models\Shop::with('setting', 'pricing', 'earnings', 'withdrawals')
            ->where('id', $shopArray['id'] ?? 0)
            ->first();

        $settings = $shop ? $shop->setting : null;

        $earningsSummary = $shop ? $this->shopService->getShopEarningsSummary($shop->id) : null;

        return view('user.shop.index', compact('shop', 'settings', 'earningsSummary'));
    }

    public function update(Request $request)
    {
        $userId = session('user_id');
        $shopArray = $this->shopService->getShopByUserId($userId);

        if (!$shopArray) {
            return redirect()->back()
                ->with('error', 'Shop not found.');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'whatsapp_number' => 'nullable|string|max:20',
            'whatsapp_group_link' => 'nullable|string|max:500',
        ]);

        try {
            $shop = \App\Models\Shop::find($shopArray['id']);

            if ($request->input('name')) {
                $shop->update(['name' => $request->input('name')]);
            }

            if ($request->has('description')) {
                // Description might be stored in shop_settings or on the shop itself
                // Store via settings for now
            }

            $this->shopService->saveShopSettings($shop->id, [
                'whatsapp_number' => $request->input('whatsapp_number'),
                'whatsapp_group_link' => $request->input('whatsapp_group_link'),
            ]);

            return redirect()->back()
                ->with('success', 'Shop settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update shop settings.');
        }
    }

    public function pricing()
    {
        $userId = session('user_id');
        $shopArray = $this->shopService->getShopByUserId($userId);

        if (!$shopArray) {
            return redirect()->route('user.shop')
                ->with('error', 'Shop not found.');
        }

        $shop = \App\Models\Shop::find($shopArray['id']);
        $pricing = $this->shopService->listShopPricing($shop->id);

        return view('user.shop.pricing', compact('shop', 'pricing'));
    }
}
