<?php

namespace App\Services;

use App\Models\UserLoginLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class UserLoginTracker
{
    public function logLogin(int $userId, string $ip, string $userAgent): int
    {
        return UserLoginLog::create([
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_status' => 'success',
        ])->id;
    }

    public function logLogout(int $userId): bool
    {
        $log = UserLoginLog::where('user_id', $userId)
            ->whereNull('logout_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$log) {
            return false;
        }

        $sessionDuration = now()->diffInSeconds($log->created_at ?? $log->login_at);

        return $log->update([
            'logout_at' => now(),
            'session_duration' => $sessionDuration,
        ]);
    }

    public function getRecentLogins(int $limit = 50): array
    {
        return UserLoginLog::with('agent:id,username,email,role')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getUserLoginStats(int $userId, int $days = 30): array
    {
        return UserLoginLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_logins'),
                DB::raw("SUM(CASE WHEN login_status = 'success' THEN 1 ELSE 0 END) as successful_logins"),
                DB::raw("SUM(CASE WHEN login_status = 'failed' THEN 1 ELSE 0 END) as failed_logins"),
                DB::raw('AVG(session_duration) as avg_session_duration')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('date')
            ->get()
            ->toArray();
    }

    public function getActiveSessions(): array
    {
        return UserLoginLog::with('agent:id,username,email,role')
            ->whereNull('logout_at')
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function cleanOldLogs(int $days = 90): array
    {
        $deletedCount = UserLoginLog::where('created_at', '<', now()->subDays($days))->delete();

        return [
            'success' => true,
            'deleted_count' => $deletedCount,
        ];
    }
}
