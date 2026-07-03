<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLoginLog;
use App\Services\UserLoginTracker;
use Illuminate\Http\Request;

class AdminUserActivityController extends Controller
{
    public function __construct(
        private UserLoginTracker $loginTracker
    ) {}

    public function index(Request $request)
    {
        $query = UserLoginLog::with('agent:id,username,email,role');

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($status = $request->input('status')) {
            $query->where('login_status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo.' 23:59:59');
        }

        if ($search = $request->input('search')) {
            $query->whereHas('agent', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $activities = $query->orderByDesc('created_at')->paginate(25);

        $activeSessions = $this->loginTracker->getActiveSessions();

        $recentLogins = $this->loginTracker->getRecentLogins(20);

        return view('admin.user-activity.index', compact('activities', 'activeSessions', 'recentLogins'));
    }
}
