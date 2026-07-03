<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Services\ReferralService;

class UserReferralController extends Controller
{
    public function __construct(
        private ReferralService $referralService
    ) {}

    public function index()
    {
        $userId = session('user_id');
        $referralCode = $this->referralService->getReferralCode($userId);
        $stats = $this->referralService->getReferralStats($userId);

        $referrals = ReferralCommission::where('referrer_id', $userId)
            ->with('referred:id,username')
            ->orderByDesc('created_at')
            ->paginate(25);

        $referralLink = url('/register?ref='.$referralCode);

        $totalReferrals = $stats['total_referrals'] ?? 0;
        $totalEarnings = $stats['total_earned'] ?? 0;

        return view('user.referrals.index', compact(
            'referralCode', 'stats', 'referrals', 'referralLink',
            'totalReferrals', 'totalEarnings'
        ));
    }
}
