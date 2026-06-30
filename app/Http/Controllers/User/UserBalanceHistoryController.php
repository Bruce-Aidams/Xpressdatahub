<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BalanceHistory;
use Illuminate\Http\Request;

class UserBalanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('user_id');
        $query = BalanceHistory::where('agent_id', $userId);

        if ($reason = $request->input('reason')) {
            $query->where('reason', $reason);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $history = $query->orderByDesc('created_at')->paginate(25);

        return view('user.balance-history.index', compact('history'));
    }
}
