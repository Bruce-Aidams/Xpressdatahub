<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BalanceHistory;
use Illuminate\Http\Request;

class AdminBalanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = BalanceHistory::with('agent:id,username');

        if ($agentId = $request->input('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        if ($reason = $request->input('reason')) {
            $query->where('reason', $reason);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('agent', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo.' 23:59:59');
        }

        $history = $query->orderByDesc('created_at')->paginate(25);

        return view('admin.balance-history.index', compact('history'));
    }
}
