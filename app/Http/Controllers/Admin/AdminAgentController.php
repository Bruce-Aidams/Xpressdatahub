<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AgentPasswordResetMail;
use App\Models\AdminUser;
use App\Models\Agent;
use App\Models\PasswordResetToken;
use App\Services\AccountStatusManager;
use App\Services\PasswordResetService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminAgentController extends Controller
{
    public function __construct(
        private AccountStatusManager $statusManager,
        private ReferralService $referralService,
        private PasswordResetService $resetService
    ) {}

    public function index(Request $request)
    {
        $query = Agent::query();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $agents = $query->withCount('orders')->orderByDesc('created_at')->paginate(25);

        return view('admin.agents.index', compact('agents'));
    }

    public function show(Agent $agent)
    {
        $agent->loadCount('orders', 'payments', 'referrerCommissions', 'referredCommissions');

        $recentOrders = $agent->orders()->orderByDesc('created_at')->limit(10)->get();
        $recentBalance = $agent->balanceHistory()->orderByDesc('created_at')->limit(10)->get();

        return view('admin.agents.show', compact('agent', 'recentOrders', 'recentBalance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:agents,username',
            'email' => 'required|email|max:255|unique:agents,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:agent,super_agent,dealers,administrator',
            'balance' => 'nullable|numeric|min:0',
        ]);

        $passwordCheck = $this->resetService->validatePasswordStrength($request->input('password'));
        if (! $passwordCheck['valid']) {
            return redirect()->back()
                ->withInput($request->except('password'))
                ->with('error', implode(' ', $passwordCheck['errors']));
        }

        try {
            $agent = Agent::create([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password_hash' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
                'balance' => $request->input('balance', 0),
                'status' => 'active',
                'registration_ip' => $request->ip(),
            ]);

            $this->referralService->generateReferralCode($agent->id);
            $this->syncAdminRole($agent);

            return redirect()->back()
                ->with('success', 'Agent created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->except('password'))
                ->with('error', 'Failed to create agent.');
        }
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email,'.$agent->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:agent,super_agent,dealers,administrator',
            'balance' => 'nullable|numeric|min:0',
        ]);

        $currentRole = $agent->role;
        $newRole = $request->input('role');

        if (($currentRole === 'administrator' || $newRole === 'administrator') && $currentRole !== $newRole) {
            if (session('admin_role') !== 'super_admin') {
                return redirect()->back()
                    ->with('error', 'Only super admins can promote users to or demote users from administrator.');
            }
        }

        try {
            $agent->update($request->only([
                'first_name', 'last_name', 'email', 'phone', 'role', 'balance',
            ]));

            $this->syncAdminRole($agent);

            return redirect()->back()
                ->with('success', 'Agent updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update agent.');
        }
    }

    public function destroy(Agent $agent)
    {
        if (session('admin_role') !== 'super_admin') {
            return redirect()->back()
                ->with('error', 'Only super admins can delete agents.');
        }

        try {
            // Also remove admin account if exists
            AdminUser::where('username', $agent->username)
                ->orWhere('email', $agent->email)
                ->delete();

            $agent->delete();

            return redirect()->back()
                ->with('success', 'Agent deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete agent.');
        }
    }

    public function updateStatus(Request $request, Agent $agent)
    {
        if ($agent->role === 'administrator' && session('admin_role') !== 'super_admin') {
            return redirect()->back()
                ->with('error', 'Only super admins can modify the status of administrators.');
        }

        $request->validate([
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        try {
            $result = $this->statusManager->updateAccountStatus(
                $agent->id,
                $request->input('status'),
                'Updated by admin'
            );

            if ($result['success']) {
                $agent->refresh();
                $this->syncAdminRole($agent);
                return redirect()->back()
                    ->with('success', 'Agent status updated successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to update status.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating status.');
        }
    }

    public function resetPassword(Request $request, Agent $agent)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validation = $this->resetService->validatePasswordStrength($request->input('password'));

        if (! $validation['valid']) {
            return redirect()->back()
                ->with('error', implode(' ', $validation['errors']));
        }

        try {
            $agent->update([
                'password_hash' => Hash::make($request->input('password')),
            ]);

            $this->syncAdminRole($agent);

            return redirect()->back()
                ->with('success', "Password updated successfully for {$agent->username}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update password.');
        }
    }

    public function sendResetLink(Agent $agent)
    {
        try {
            $token = $this->resetService->generateToken();
            $otp = $this->resetService->generateOTP();

            PasswordResetToken::create([
                'email' => $agent->email,
                'token_hash' => password_hash($token, PASSWORD_DEFAULT),
                'otp_code' => $otp,
                'expires_at' => now()->addMinutes(60),
                'max_attempts' => 5,
            ]);

            $agentName = $agent->first_name.' '.$agent->last_name;
            Mail::to($agent->email)->send(new AgentPasswordResetMail($token, $agent->email, $agentName));

            return redirect()->back()
                ->with('success', "Password reset link sent to {$agent->email}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send reset link. Please try again.');
        }
    }

    public function pendingApprovals()
    {
        $pendingAgents = Agent::where('is_approved', false)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.agents.pending', compact('pendingAgents'));
    }

    public function approve(Agent $agent)
    {
        try {
            $agent->update([
                'is_approved' => true,
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', "{$agent->username} has been approved successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to approve agent.');
        }
    }

    public function reject(Agent $agent)
    {
        try {
            $agent->update([
                'status' => 'suspended',
                'updated_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', "{$agent->username} has been rejected.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject agent.');
        }
    }

    public function makeAdmin(Agent $agent)
    {
        if (session('admin_role') !== 'super_admin') {
            return redirect()->back()
                ->with('error', 'Only super admins can promote agents to administrator.');
        }

        try {
            $agent->update(['role' => 'administrator']);
            $this->syncAdminRole($agent);

            return redirect()->back()
                ->with('success', "{$agent->username} has been promoted to administrator successfully.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to promote agent to admin: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to promote agent to administrator.');
        }
    }

    protected function syncAdminRole(Agent $agent)
    {
        $existingAdmin = AdminUser::where('username', $agent->username)
            ->orWhere('email', $agent->email)
            ->first();

        $isActive = ($agent->role === 'administrator' && $agent->status === 'active');

        if ($agent->role === 'administrator') {
            if (! $existingAdmin) {
                AdminUser::create([
                    'username' => $agent->username,
                    'email' => $agent->email,
                    'password_hash' => $agent->password_hash,
                    'full_name' => trim($agent->first_name . ' ' . $agent->last_name),
                    'role' => 'admin',
                    'is_active' => $isActive,
                ]);
            } else {
                $existingAdmin->update([
                    'username' => $agent->username,
                    'email' => $agent->email,
                    'password_hash' => $agent->password_hash,
                    'full_name' => trim($agent->first_name . ' ' . $agent->last_name),
                    'is_active' => $isActive,
                ]);
            }
        } else {
            if ($existingAdmin) {
                $existingAdmin->update([
                    'is_active' => false,
                ]);
            }
        }
    }
}
