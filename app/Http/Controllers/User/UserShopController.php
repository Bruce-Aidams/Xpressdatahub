<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomPricing;
use App\Models\Shop;
use App\Models\ShopPricing;
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

        // Use a direct Eloquent query to always get the freshest is_active value
        $shop = Shop::with('setting', 'pricing', 'earnings', 'withdrawals')
            ->where('user_id', $userId)
            ->first();

        if (! $shop) {
            return view('user.shop.create');
        }

        if (! $shop->is_active) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your shop has been deactivated. Please contact support.');
        }

        $settings = $shop->setting;
        $earningsSummary = $this->shopService->getShopEarningsSummary($shop->id);

        return view('user.shop.index', compact('shop', 'settings', 'earningsSummary'));
    }

    public function store(Request $request)
    {
        $userId = session('user_id');
        $shopArray = $this->shopService->getShopByUserId($userId);

        if ($shopArray) {
            return redirect()->route('user.shop.index')->with('info', 'You already have a shop.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'whatsapp_number' => 'nullable|string|max:20',
            'whatsapp_group_link' => 'nullable|string|max:500',
        ]);

        try {
            $agent = Agent::find($userId);
            $shopArray = $this->shopService->createShopForUser($userId, $agent->username ?? 'user');
            $shop = Shop::find($shopArray['id']);
            
            $shop->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'is_active' => true
            ]);
            
            $this->shopService->saveShopSettings($shop->id, [
                'whatsapp_number' => $request->input('whatsapp_number'),
                'whatsapp_group_link' => $request->input('whatsapp_group_link'),
            ]);

            return redirect()->route('user.shop.index')->with('success', 'Shop created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create shop.');
        }
    }

    public function update(Request $request)
    {
        $userId = session('user_id');

        $shop = Shop::where('user_id', $userId)->first();

        if (! $shop) {
            return redirect()->back()
                ->with('error', 'Shop not found.');
        }

        if (! $shop->is_active) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your shop has been deactivated. Please contact support.');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'whatsapp_number' => 'nullable|string|max:20',
            'whatsapp_group_link' => 'nullable|string|max:500',
        ]);

        try {
            if ($request->input('name')) {
                $shop->update(['name' => $request->input('name')]);
            }

            if ($request->has('description')) {
                $shop->update(['description' => $request->input('description')]);
            }

            $this->shopService->saveShopSettings($shop->id, [
                'whatsapp_number' => $request->input('whatsapp_number'),
                'whatsapp_group_link' => $request->input('whatsapp_group_link'),
                'working_hours' => $request->input('working_hours'),
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

        $shop = Shop::where('user_id', $userId)->first();

        if (! $shop) {
            return redirect()->route('user.shop.index')
                ->with('error', 'Shop not found.');
        }

        if (! $shop->is_active) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your shop has been deactivated. Please contact support.');
        }

        $pricing = $this->shopService->listShopPricing($shop->id);

        // Packages already added to this shop (keyed by package_size + network_type)
        $existingKeys = $pricing->map(fn($p) => $p->package_size . '|' . $p->network_type)->toArray();

        // All admin-defined packages not yet in the shop
        $availablePackages = CustomPricing::where('is_active', true)
            ->orderBy('network_type')
            ->orderBy('package_size_gb')
            ->get()
            ->filter(fn($cp) => ! in_array($cp->package_size . '|' . $cp->network_type, $existingKeys))
            ->values();

        return view('user.shop.pricing', compact('shop', 'pricing', 'availablePackages'));
    }

    public function updatePricing(Request $request, $pricingId)
    {
        $userId = session('user_id');

        $shop = Shop::where('user_id', $userId)->first();

        if (! $shop) {
            return redirect()->route('user.shop.index')->with('error', 'Shop not found.');
        }

        if (! $shop->is_active) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your shop has been deactivated. Please contact support.');
        }

        $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        $result = $this->shopService->updateShopPricing($shop->id, (int) $pricingId, [
            'selling_price' => $request->input('selling_price'),
        ]);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Pricing updated successfully.');
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function addPackage(Request $request)
    {
        $userId = session('user_id');

        $shop = Shop::where('user_id', $userId)->first();

        if (! $shop) {
            return redirect()->route('user.shop.index')->with('error', 'Shop not found.');
        }

        if (! $shop->is_active) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your shop has been deactivated. Please contact support.');
        }

        $request->validate([
            'custom_pricing_id' => 'required|integer|exists:custom_pricing,id',
            'selling_price'     => 'required|numeric|min:0',
        ]);

        $source = CustomPricing::find($request->input('custom_pricing_id'));

        if (! $source) {
            return redirect()->back()->with('error', 'Package not found.');
        }

        $basePrice = floatval($source->cost);
        $sellPrice = floatval($request->input('selling_price'));

        if ($sellPrice < $basePrice) {
            return redirect()->back()->with('error', 'Selling price cannot be below the base price (GH₵'.number_format($basePrice, 2).').');
        }

        // Check not already added
        $already = ShopPricing::where('shop_id', $shop->id)
            ->where('package_size', $source->package_size)
            ->where('network_type', $source->network_type)
            ->exists();

        if ($already) {
            return redirect()->back()->with('info', 'Package already exists in your shop.');
        }

        ShopPricing::create([
            'shop_id'        => $shop->id,
            'package_size'   => $source->package_size,
            'package_size_gb'=> floatval($source->package_size_gb),
            'network_type'   => $source->network_type,
            'base_price'     => $basePrice,
            'selling_price'  => $sellPrice,
            'profit'         => round($sellPrice - $basePrice, 2),
        ]);

        return redirect()->back()->with('success', 'Package added to your shop successfully!');
    }

    public function removePackage(Request $request, $pricingId)
    {
        $userId = session('user_id');

        $shop = Shop::where('user_id', $userId)->first();

        if (! $shop) {
            return redirect()->route('user.shop.index')->with('error', 'Shop not found.');
        }

        $deleted = ShopPricing::where('shop_id', $shop->id)
            ->where('id', $pricingId)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Package removed from your shop.');
        }

        return redirect()->back()->with('error', 'Package not found.');
    }
}
