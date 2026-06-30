<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopWithdrawal;
use App\Services\ShopService;
use Illuminate\Http\Request;

class AdminShopWithdrawalController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index(Request $request)
    {
        $query = ShopWithdrawal::with('shop:id,shop_slug', 'agent:id,username');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('agent', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%");
            });
        }

        $withdrawals = $query->orderByDesc('created_at')->paginate(25);

        return view('admin.shop-withdrawals.index', compact('withdrawals'));
    }

    public function approve(Request $request, ShopWithdrawal $shopWithdrawal)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->shopService->approveWithdrawal(
                $shopWithdrawal->id,
                $request->input('admin_note')
            );

            if ($result) {
                return redirect()->back()
                    ->with('success', 'Withdrawal approved successfully.');
            }

            return redirect()->back()
                ->with('error', 'Failed to approve withdrawal.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while approving withdrawal.');
        }
    }

    public function reject(Request $request, ShopWithdrawal $shopWithdrawal)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->shopService->rejectWithdrawal(
                $shopWithdrawal->id,
                $request->input('admin_note')
            );

            if ($result) {
                return redirect()->back()
                    ->with('success', 'Withdrawal rejected.');
            }

            return redirect()->back()
                ->with('error', 'Failed to reject withdrawal.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while rejecting withdrawal.');
        }
    }

    public function complete(Request $request, ShopWithdrawal $shopWithdrawal)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->shopService->completeWithdrawal($shopWithdrawal->id);

            if ($result) {
                if ($request->input('admin_note')) {
                    $shopWithdrawal->update(['admin_note' => $request->input('admin_note')]);
                }
                return redirect()->back()
                    ->with('success', 'Withdrawal marked as completed.');
            }

            return redirect()->back()
                ->with('error', 'Failed to complete withdrawal.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while completing withdrawal.');
        }
    }
}
