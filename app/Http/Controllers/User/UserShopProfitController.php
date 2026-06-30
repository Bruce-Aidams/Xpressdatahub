<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShopEarning;
use App\Models\ShopWithdrawal;
use App\Services\ShopService;
use Illuminate\Http\Request;

class UserShopProfitController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index(Request $request)
    {
        $userId = session('user_id');
        $shop = $this->shopService->getShopByUserId($userId);

        if (!$shop) {
            return redirect()->route('user.shop')
                ->with('error', 'Shop not found.');
        }

        $query = ShopEarning::where('shop_id', $shop['id']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $earnings = $query->orderByDesc('created_at')->paginate(25);

        $summary = $this->shopService->getShopEarningsSummary($shop['id']);

        $withdrawals = ShopWithdrawal::where('shop_id', $shop['id'])
            ->orderByDesc('created_at')
            ->paginate(25);

        $totalProfit = $summary['credited_profit'] + $summary['pending_profit'];
        $totalWithdrawn = $summary['completed_withdrawals'];
        $available = $summary['available_balance'];

        return view('user.shop-profits.index', compact('shop', 'earnings', 'summary', 'withdrawals', 'totalProfit', 'totalWithdrawn', 'available'));
    }
}
