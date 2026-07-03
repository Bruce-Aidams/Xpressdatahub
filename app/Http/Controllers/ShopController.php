<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\ShopService;

class ShopController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function show($slug)
    {
        $shopData = $this->shopService->getShopBySlug($slug);

        if (! $shopData) {
            abort(404, 'Shop not found.');
        }

        $shop = Shop::with('agent:id,username,role', 'setting', 'pricing')
            ->where('shop_slug', $slug)
            ->first();

        if (! $shop || ! $shop->is_active) {
            abort(404, 'Shop is not available.');
        }

        return view('shop.show', compact('shop', 'shopData'));
    }
}
