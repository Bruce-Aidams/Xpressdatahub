<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class AdminAuthService
{
    public function authenticate(string $username, string $password): array
    {
        $admin = AdminUser::where('username', $username)
            ->where('is_active', true)
            ->first();

        if (!$admin) {
            $this->logLogin(null, $username, 'failed', 'User not found');
            return ['success' => false, 'error' => 'Invalid credentials'];
        }

        if (!Hash::check($password, $admin->password_hash)) {
            $this->logLogin($admin->id, $username, 'failed', 'Invalid password');
            return ['success' => false, 'error' => 'Invalid credentials'];
        }

        $admin->update(['last_login_at' => now()]);

        $this->logLogin($admin->id, $username, 'success');

        Session::regenerate();
        Session::put('admin_logged_in', true);
        Session::put('admin_id', $admin->id);
        Session::put('admin_username', $admin->username);
        Session::put('admin_role', $admin->role);
        Session::put('login_time', now()->timestamp);

        return [
            'success' => true,
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
            ],
        ];
    }

    public function isAuthenticated(): bool
    {
        return Session::get('admin_logged_in') === true
            && Session::has('admin_id');
    }

    public function requireAuth(string $redirectTo = '/admin/login'): void
    {
        if (!$this->isAuthenticated()) {
            redirect($redirectTo)->send();
            exit();
        }

        $loginTime = Session::get('login_time');
        if ($loginTime && (now()->timestamp - $loginTime) > 7200) {
            $this->logout();
            redirect($redirectTo . '?timeout=1')->send();
            exit();
        }
    }

    public function logout(): void
    {
        if (Session::has('admin_username')) {
            $this->logLogin(
                Session::get('admin_id'),
                Session::get('admin_username'),
                'logout'
            );
        }

        Session::invalidate();
        Session::regenerateToken();
    }

    public function createAdmin(array $data): array
    {
        if (AdminUser::where('username', $data['username'])->exists()) {
            return ['success' => false, 'error' => 'Username already exists'];
        }

        $admin = AdminUser::create([
            'username' => $data['username'],
            'password_hash' => Hash::make($data['password']),
            'email' => $data['email'] ?? null,
            'full_name' => $data['full_name'] ?? null,
            'role' => $data['role'] ?? 'admin',
            'is_active' => true,
        ]);

        return ['success' => true, 'admin_id' => $admin->id];
    }

    public function changePassword(int $adminId, string $currentPassword, string $newPassword): array
    {
        $admin = AdminUser::find($adminId);
        if (!$admin) {
            return ['success' => false, 'error' => 'Admin not found'];
        }

        if (!Hash::check($currentPassword, $admin->password_hash)) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }

        $admin->update([
            'password_hash' => Hash::make($newPassword),
            'updated_at' => now(),
        ]);

        $this->logPasswordChange($adminId);

        return ['success' => true];
    }

    public function getAdminInfo(int $id): ?array
    {
        $admin = AdminUser::select(
            'id', 'username', 'email', 'full_name', 'role',
            'is_active', 'last_login_at', 'created_at'
        )->find($id);

        return $admin ? $admin->toArray() : null;
    }

    public function getLoginLogs(int $limit = 50): array
    {
        return DB::table('admin_login_logs')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function logLogin(?int $adminId, string $username, string $status, ?string $reason = null): void
    {
        $ip = Request::ip();
        $userAgent = substr(Request::userAgent() ?? 'unknown', 0, 500);

        try {
            DB::table('admin_login_logs')->insert([
                'admin_id' => $adminId,
                'username' => $username,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'login_status' => $status,
                'failure_reason' => $reason,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }

    private function logPasswordChange(int $adminId): void
    {
        $ip = Request::ip();
        $userAgent = substr(Request::userAgent() ?? 'unknown', 0, 500);
        $username = Session::get('admin_username', 'unknown');

        try {
            DB::table('admin_login_logs')->insert([
                'admin_id' => $adminId,
                'username' => $username,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'login_status' => 'password_changed',
                'failure_reason' => 'Password changed successfully',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
