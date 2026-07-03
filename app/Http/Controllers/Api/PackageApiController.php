<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomPricing;
use Illuminate\Http\Request;

class PackageApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'network_type' => 'nullable|string|in:MTN,Telecel,AirtelTigo',
        ]);

        $query = CustomPricing::where('is_active', true);

        if ($network = $request->input('network_type')) {
            $query->where('network_type', $network);
        }

        $packages = $query->orderBy('network_type')
            ->orderBy('package_size_gb')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'network_type' => $p->network_type,
                'package_size' => $p->package_size,
                'package_size_gb' => $p->package_size_gb,
                'selling_price' => floatval($p->selling_price),
                'cost' => floatval($p->cost),
            ]);

        return response()->json([
            'success' => true,
            'packages' => $packages,
            'total' => $packages->count(),
        ]);
    }
}
