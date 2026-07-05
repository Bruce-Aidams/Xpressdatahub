<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Agent;
use App\Models\ApiConfig;
use App\Models\ApiKey;
use App\Models\BalanceHistory;
use App\Models\BannerNotification;
use App\Models\CustomPricing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReferralConfig;
use App\Models\Shop;
use App\Models\ShopWithdrawal;
use App\Models\UserLoginLog;
use App\Services\AccountStatusManager;
use App\Services\AdminAuthService;
use App\Services\AdminNotificationService;
use App\Services\BannerNotificationService;
use App\Services\CustomPricingService;
use App\Services\DataIntegrationService;
use App\Services\LowBalanceAlertService;
use App\Services\MinimumTopupManager;
use App\Services\OrderService;
use App\Services\PasswordResetService;
use App\Services\PaymentConfigService;
use App\Services\PaystackChargeManager;
use App\Services\ReferralService;
use App\Services\ShopService;
use App\Services\UserLoginTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminApiController extends Controller
{
    public function __construct(
        private AccountStatusManager $statusManager,
        private AdminAuthService $authService,
        private BannerNotificationService $bannerService,
        private CustomPricingService $pricingService,
        private DataIntegrationService $integrationService,
        private LowBalanceAlertService $alertService,
        private MinimumTopupManager $topupManager,
        private OrderService $orderService,
        private PasswordResetService $passwordService,
        private PaystackChargeManager $chargeManager,
        private PaymentConfigService $configService,
        private ReferralService $referralService,
        private ShopService $shopService,
        private UserLoginTracker $loginTracker,
    ) {}

    private function admin(Request $request): AdminUser
    {
        return $request->attributes->get('admin_user');
    }

    // ─── Auth ───────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = AdminUser::where('username', $request->input('username'))->first();

        if (! $admin || ! Hash::check($request->input('password'), $admin->password_hash)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }

        if (! $admin->is_active) {
            return response()->json(['success' => false, 'message' => 'Account is deactivated.'], 403);
        }

        if (! $admin->api_token) {
            $admin->update(['api_token' => Str::random(64), 'last_login_at' => now()]);
        } else {
            $admin->update(['last_login_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'token' => $admin->api_token,
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
                'full_name' => $admin->full_name,
                'role' => $admin->role,
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $admin = $this->admin($request);

        return response()->json([
            'success' => true,
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
                'full_name' => $admin->full_name,
                'role' => $admin->role,
                'last_login_at' => $admin->last_login_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:admin_users,email,'.$this->admin($request)->id,
        ]);

        $admin = $this->admin($request);
        $admin->update($request->only(['full_name', 'email']));

        return response()->json(['success' => true, 'message' => 'Profile updated.']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordCheck = $this->passwordService->validatePasswordStrength($request->input('password'));
        if (! $passwordCheck['valid']) {
            return response()->json(['success' => false, 'message' => implode(' ', $passwordCheck['errors'])], 422);
        }

        $result = $this->authService->changePassword(
            $this->admin($request)->id,
            $request->input('current_password'),
            $request->input('password')
        );

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Password changed.']);
        }

        return response()->json(['success' => false, 'message' => $result['error'] ?? 'Failed.'], 400);
    }

    // ─── Dashboard ──────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $totalUsers = Agent::count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->count();
        $totalRevenue = Order::whereIn('status', ['completed', 'delivered'])->sum('amount');
        $totalShops = Shop::count();
        $activeShops = Shop::where('is_active', true)->count();
        $pendingWithdrawals = ShopWithdrawal::where('status', 'pending')->count();

        $recentOrders = Order::with('agent:id,username')
            ->orderByDesc('created_at')->limit(10)->get()
            ->map(fn ($o) => [
                'id' => $o->id, 'status' => $o->status, 'phone_number' => $o->phone_number,
                'network_type' => $o->network_type, 'package_size' => $o->package_size,
                'amount' => $o->amount, 'agent' => $o->agent?->username,
                'created_at' => $o->created_at?->format('Y-m-d H:i:s'),
            ]);

        $recentUsers = Agent::orderByDesc('created_at')->limit(10)->get()
            ->map(fn ($u) => [
                'id' => $u->id, 'username' => $u->username, 'email' => $u->email,
                'role' => $u->role, 'status' => $u->status,
                'created_at' => $u->created_at?->format('Y-m-d H:i:s'),
            ]);

        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())->whereIn('status', ['completed', 'delivered'])->sum('amount');
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->whereIn('status', ['completed', 'delivered'])->sum('amount');

        $weeklyOrders = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $weeklyOrders->push([
                'day' => $day->format('D'),
                'count' => Order::whereDate('created_at', $day)->count(),
            ]);
        }

        $networkStats = Order::select('network_type', DB::raw('count(*) as total'))
            ->whereNotNull('network_type')->groupBy('network_type')->orderByDesc('total')->get();

        $topPackages = Order::select('network_type', 'package_size', DB::raw('count(*) as total'))
            ->whereNotNull('package_size')->groupBy('network_type', 'package_size')
            ->orderByDesc('total')->limit(3)->get();

        return response()->json([
            'success' => true,
            'stats' => compact('totalUsers', 'totalOrders', 'pendingOrders', 'completedOrders',
                'totalRevenue', 'totalShops', 'activeShops', 'pendingWithdrawals',
                'todayOrders', 'todayRevenue', 'monthlyRevenue'),
            'recent_orders' => $recentOrders,
            'recent_users' => $recentUsers,
            'weekly_orders' => $weeklyOrders,
            'network_stats' => $networkStats,
            'top_packages' => $topPackages,
        ]);
    }

    // ─── Agents ─────────────────────────────────────────────

    public function listAgents(Request $request)
    {
        $query = Agent::query();

        if ($s = $request->input('status')) {
            $query->where('status', $s);
        }
        if ($r = $request->input('role')) {
            $query->where('role', $r);
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

        $agents = $query->withCount('orders')->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'agents' => $agents,
        ]);
    }

    public function showAgent(Agent $agent)
    {
        $agent->loadCount('orders', 'payments', 'referrerCommissions', 'referredCommissions');
        $recentOrders = $agent->orders()->orderByDesc('created_at')->limit(10)->get();
        $recentBalance = $agent->balanceHistory()->orderByDesc('created_at')->limit(10)->get();

        return response()->json([
            'success' => true,
            'agent' => $agent,
            'recent_orders' => $recentOrders,
            'recent_balance' => $recentBalance,
        ]);
    }

    public function createAgent(Request $request)
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

        $passwordCheck = $this->passwordService->validatePasswordStrength($request->input('password'));
        if (! $passwordCheck['valid']) {
            return response()->json(['success' => false, 'message' => implode(' ', $passwordCheck['errors'])], 422);
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Agent created.',
            'agent' => $agent->only(['id', 'username', 'email', 'role', 'status']),
        ], 201);
    }

    public function updateAgent(Request $request, Agent $agent)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email,'.$agent->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:agent,super_agent,dealers,administrator',
            'balance' => 'nullable|numeric|min:0',
        ]);

        $agent->update($request->only(['first_name', 'last_name', 'email', 'phone', 'role', 'balance']));

        return response()->json(['success' => true, 'message' => 'Agent updated.']);
    }

    public function deleteAgent(Agent $agent)
    {
        $agent->delete();

        return response()->json(['success' => true, 'message' => 'Agent deleted.']);
    }

    public function updateAgentStatus(Request $request, Agent $agent)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $result = $this->statusManager->updateAccountStatus($agent->id, $request->input('status'), 'Updated via API');

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Agent status updated.']);
        }

        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Failed.'], 400);
    }

    // ─── Orders ─────────────────────────────────────────────

    public function listOrders(Request $request)
    {
        $query = Order::with('agent:id,username');

        if ($s = $request->input('status')) {
            $query->where('status', $s);
        }
        if ($n = $request->input('network_type')) {
            $query->where('network_type', $n);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('agent', fn ($q2) => $q2->where('username', 'like', "%{$search}%"));
            });
        }
        if ($df = $request->input('date_from')) {
            $query->where('created_at', '>=', $df);
        }
        if ($dt = $request->input('date_to')) {
            $query->where('created_at', '<=', $dt.' 23:59:59');
        }

        $orders = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    public function showOrder(Order $order)
    {
        $order->load('agent:id,username,phone,email', 'payment', 'statusHistory');

        return response()->json(['success' => true, 'order' => $order]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,delivered,failed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $result = $this->orderService->updateOrderStatus($order->id, $request->input('status'), $request->input('notes'));

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Order status updated.']);
        }

        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Failed.'], 400);
    }

    public function allOrders(Request $request)
    {
        $query = Order::with('agent:id,username,phone');

        if ($s = $request->input('status')) {
            $query->where('status', $s);
        }
        if ($n = $request->input('network_type')) {
            $query->where('network_type', $n);
        }
        if ($src = $request->input('order_source')) {
            $query->where('order_source', $src);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('order_reference', 'like', "%{$search}%")
                    ->orWhereHas('agent', fn ($q2) => $q2->where('username', 'like', "%{$search}%"));
            });
        }
        if ($df = $request->input('date_from')) {
            $query->where('created_at', '>=', $df);
        }
        if ($dt = $request->input('date_to')) {
            $query->where('created_at', '<=', $dt.' 23:59:59');
        }
        if ($min = $request->input('min_amount')) {
            $query->where('amount', '>=', $min);
        }
        if ($max = $request->input('max_amount')) {
            $query->where('amount', '<=', $max);
        }

        $orders = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));
        $totalAmount = (clone $query)->sum('amount');
        $totalCount = (clone $query)->count();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'summary' => ['total_amount' => $totalAmount, 'total_count' => $totalCount],
        ]);
    }

    // ─── Shops ──────────────────────────────────────────────

    public function listShops(Request $request)
    {
        $shops = Shop::with('agent:id,username,role')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'shops' => $shops]);
    }

    public function showShop(Shop $shop)
    {
        $shop->load('agent:id,username,role', 'setting', 'pricing', 'earnings', 'withdrawals');
        $totalEarnings = $shop->earnings()->where('status', 'credited')->sum('profit');
        $pendingEarnings = $shop->earnings()->where('status', 'pending')->sum('profit');
        $totalWithdrawn = $shop->withdrawals()->whereIn('status', ['completed', 'delivered'])->sum('amount');

        return response()->json([
            'success' => true, 'shop' => $shop,
            'total_earnings' => $totalEarnings, 'pending_earnings' => $pendingEarnings,
            'total_withdrawn' => $totalWithdrawn,
        ]);
    }

    public function updateShopStatus(Request $request, Shop $shop)
    {
        $request->validate(['is_active' => 'required|boolean']);
        $shop->update(['is_active' => $request->boolean('is_active'), 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Shop status updated.']);
    }

    // ─── Shop Orders ────────────────────────────────────────

    public function listShopOrders(Request $request)
    {
        $query = Order::with('shop:id,shop_slug', 'agent:id,username')
            ->where('order_source', 'shop');

        if ($s = $request->input('status')) {
            $query->where('status', $s);
        }
        if ($shopId = $request->input('shop_id')) {
            $query->where('shop_id', $shopId);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('order_reference', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($q2) => $q2->where('shop_slug', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    public function verifyShopOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'paystack_txn_id' => 'nullable|string|max:255',
        ]);

        $adminId = $this->admin($request)->id;
        $adminUsername = $this->admin($request)->username;

        $result = $this->shopService->adminFinalizeShopOrderPayment(
            $request->input('order_id'), $adminId, $adminUsername, $request->input('paystack_txn_id')
        );

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    // ─── Shop Withdrawals ───────────────────────────────────

    public function listShopWithdrawals(Request $request)
    {
        $query = ShopWithdrawal::with('shop:id,shop_slug', 'agent:id,username');
        if ($s = $request->input('status')) {
            $query->where('status', $s);
        }
        if ($search = $request->input('search')) {
            $query->whereHas('agent', fn ($q) => $q->where('username', 'like', "%{$search}%"));
        }

        $withdrawals = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'withdrawals' => $withdrawals]);
    }

    public function approveWithdrawal(Request $request, ShopWithdrawal $shopWithdrawal)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);
        $result = $this->shopService->approveWithdrawal($shopWithdrawal->id, $request->input('admin_note'));
        if ($result) {
            return response()->json(['success' => true, 'message' => 'Withdrawal approved.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed.'], 400);
    }

    public function rejectWithdrawal(Request $request, ShopWithdrawal $shopWithdrawal)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);
        $result = $this->shopService->rejectWithdrawal($shopWithdrawal->id, $request->input('admin_note'));
        if ($result) {
            return response()->json(['success' => true, 'message' => 'Withdrawal rejected.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed.'], 400);
    }

    // ─── Pricing ────────────────────────────────────────────

    public function listPricing(Request $request)
    {
        $pricing = CustomPricing::orderBy('package_size_gb')->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'pricing' => $pricing]);
    }

    public function createPricing(Request $request)
    {
        $request->validate([
            'package_size' => 'required|string|max:50',
            'network_type' => 'required|string|max:50',
            'cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'user_role' => 'required|string|in:agent,super_agent,dealers,administrator,all',
        ]);

        $result = $this->pricingService->setCustomPricing([
            'package_size' => $request->input('package_size'),
            'network_type' => $request->input('network_type'),
            'cost' => $request->input('cost'),
            'selling_price' => $request->input('selling_price'),
            'user_role' => $request->input('user_role'),
            'created_by' => $this->admin($request)->id,
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Pricing saved.'], 201);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    public function updatePricing(Request $request, CustomPricing $customPricing)
    {
        $request->validate([
            'cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $customPricing->update([
            'cost' => $request->input('cost'),
            'selling_price' => $request->input('selling_price', $customPricing->selling_price),
            'is_active' => $request->boolean('is_active', $customPricing->is_active),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Pricing updated.']);
    }

    public function togglePricing(CustomPricing $customPricing)
    {
        $customPricing->update(['is_active' => ! $customPricing->is_active, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Pricing status toggled.']);
    }

    public function deletePricing(CustomPricing $customPricing)
    {
        $customPricing->delete();

        return response()->json(['success' => true, 'message' => 'Pricing deleted.']);
    }

    // ─── Accounts ───────────────────────────────────────────

    public function listAccounts(Request $request)
    {
        $query = Agent::query();
        if ($s = $request->input('status')) {
            $query->where('status', $s);
        }
        if ($r = $request->input('role')) {
            $query->where('role', $r);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    public function updateAccountStatus(Request $request, Agent $agent)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,suspended',
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->statusManager->updateAccountStatus($agent->id, $request->input('status'), $request->input('reason', 'Updated via API'));
        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Account status updated.']);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    // ─── Banners ────────────────────────────────────────────

    public function listBanners(Request $request)
    {
        $banners = BannerNotification::orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'banners' => $banners]);
    }

    public function createBanner(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'speed' => 'nullable|integer|min:10|max:200',
            'color' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
        ]);

        $result = $this->bannerService->saveBanner([
            'message' => $request->input('message'),
            'is_enabled' => $request->boolean('is_enabled', false),
            'speed' => $request->input('speed', 50),
            'color' => $request->input('color', 'blue'),
            'background_color' => $request->input('background_color', 'blue'),
            'text_color' => $request->input('text_color', 'white'),
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Banner created.'], 201);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    public function updateBanner(Request $request, BannerNotification $bannerNotification)
    {
        $request->validate([
            'message' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'speed' => 'nullable|integer|min:10|max:200',
            'color' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
        ]);

        $result = $this->bannerService->saveBanner([
            'id' => $bannerNotification->id,
            'message' => $request->input('message'),
            'is_enabled' => $request->boolean('is_enabled', $bannerNotification->is_enabled),
            'speed' => $request->input('speed', $bannerNotification->speed ?? 50),
            'color' => $request->input('color', $bannerNotification->color ?? 'blue'),
            'background_color' => $request->input('background_color', $bannerNotification->background_color ?? 'blue'),
            'text_color' => $request->input('text_color', $bannerNotification->text_color ?? 'white'),
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Banner updated.']);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    public function deleteBanner(BannerNotification $bannerNotification)
    {
        $result = $this->bannerService->deleteBanner($bannerNotification->id);
        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Banner deleted.']);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    // ─── API Keys ───────────────────────────────────────────

    public function listApiKeys(Request $request)
    {
        $keys = ApiKey::with('agent:id,username')->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'api_keys' => $keys]);
    }

    public function createApiKey(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:agents,id',
            'rate_limit' => 'nullable|integer|min:1',
            'permissions' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $apiKey = ApiKey::create([
            'user_id' => $request->input('user_id'),
            'name' => $request->input('name'),
            'api_key' => 'mk_'.Str::random(32),
            'api_secret' => Str::random(64),
            'is_active' => true,
            'rate_limit' => $request->input('rate_limit', 100),
            'permissions' => $request->input('permissions', ['orders:create', 'orders:read']),
            'expires_at' => $request->input('expires_at'),
        ]);

        return response()->json([
            'success' => true, 'message' => 'API key created.',
            'api_key' => $apiKey->only(['id', 'name', 'api_key', 'is_active', 'rate_limit', 'permissions']),
        ], 201);
    }

    public function updateApiKey(Request $request, ApiKey $apiKey)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'rate_limit' => 'nullable|integer|min:1',
            'permissions' => 'nullable|array',
        ]);

        $apiKey->update($request->only(['name', 'is_active', 'rate_limit', 'permissions']));

        return response()->json(['success' => true, 'message' => 'API key updated.']);
    }

    public function deleteApiKey(ApiKey $apiKey)
    {
        $apiKey->delete();

        return response()->json(['success' => true, 'message' => 'API key deleted.']);
    }

    // ─── Analytics ──────────────────────────────────────────

    public function analytics(Request $request)
    {
        $totalRevenue = Order::whereIn('status', ['completed', 'delivered'])->sum('amount');
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->whereIn('status', ['completed', 'delivered'])->sum('amount');
        $dailyRevenue = Order::whereDate('created_at', today())->whereIn('status', ['completed', 'delivered'])->sum('amount');

        $totalOrders = Order::count();
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $failedOrders = Order::where('status', 'failed')->count();

        $totalUsers = Agent::count();
        $activeUsers = Agent::where('status', 'active')->count();
        $newUsersToday = Agent::whereDate('created_at', today())->count();

        $revenueByNetwork = Order::whereIn('status', ['completed', 'delivered'])
            ->select('network_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('network_type')->get();

        $topUsers = Agent::withCount('orders')
            ->withSum('orders', 'amount')
            ->whereHas('orders', fn ($q) => $q->whereIn('status', ['completed', 'delivered']))
            ->orderByDesc('orders_sum_amount')->limit(10)->get();

        $chartData = collect();
        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $rev = Order::whereDate('created_at', $day)->whereIn('status', ['completed', 'delivered'])->sum('amount');
            $chartData->push(['date' => $day->format('M d'), 'revenue' => $rev]);
        }

        return response()->json([
            'success' => true,
            'analytics' => compact('totalRevenue', 'monthlyRevenue', 'dailyRevenue',
                'totalOrders', 'completedOrders', 'pendingOrders', 'failedOrders',
                'totalUsers', 'activeUsers', 'newUsersToday',
                'revenueByNetwork', 'topUsers', 'chartData'),
        ]);
    }

    // ─── Balance History ────────────────────────────────────

    public function listBalanceHistory(Request $request)
    {
        $query = BalanceHistory::with('agent:id,username');
        if ($agentId = $request->input('agent_id')) {
            $query->where('agent_id', $agentId);
        }
        if ($reason = $request->input('reason')) {
            $query->where('reason', $reason);
        }
        if ($search = $request->input('search')) {
            $query->whereHas('agent', fn ($q) => $q->where('username', 'like', "%{$search}%"));
        }
        if ($df = $request->input('date_from')) {
            $query->where('created_at', '>=', $df);
        }
        if ($dt = $request->input('date_to')) {
            $query->where('created_at', '<=', $dt.' 23:59:59');
        }

        $history = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'history' => $history]);
    }

    // ─── User Activity ──────────────────────────────────────

    public function listUserActivity(Request $request)
    {
        $query = UserLoginLog::with('agent:id,username,email,role');
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($status = $request->input('status')) {
            $query->where('login_status', $status);
        }
        if ($df = $request->input('date_from')) {
            $query->where('created_at', '>=', $df);
        }
        if ($dt = $request->input('date_to')) {
            $query->where('created_at', '<=', $dt.' 23:59:59');
        }
        if ($search = $request->input('search')) {
            $query->whereHas('agent', fn ($q) => $q->where('username', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $activities = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));
        $activeSessions = $this->loginTracker->getActiveSessions();
        $recentLogins = $this->loginTracker->getRecentLogins(20);

        return response()->json([
            'success' => true, 'activities' => $activities,
            'active_sessions' => $activeSessions, 'recent_logins' => $recentLogins,
        ]);
    }

    // ─── Notifications ──────────────────────────────────────

    public function listNotifications(Request $request)
    {
        $service = app(AdminNotificationService::class);
        $notifications = $service->getAllNotifications(['limit' => $request->input('limit', 50)]);

        return response()->json(['success' => true, 'notifications' => $notifications]);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|string|in:all,agents,super_agents,dealers,administrators,specific,admin',
            'recipient_ids' => 'nullable|array',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        $service = app(AdminNotificationService::class);
        $result = $service->sendNotification([
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'sender_id' => $this->admin($request)->id,
            'recipient_type' => $request->input('recipient_type'),
            'recipient_ids' => $request->input('recipient_ids'),
            'priority' => $request->input('priority', 'normal'),
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Notification sent.'], 201);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    // ─── Config: API ────────────────────────────────────────

    public function getApiConfig(Request $request)
    {
        $configs = ApiConfig::all();

        return response()->json(['success' => true, 'configs' => $configs]);
    }

    public function updateApiConfig(Request $request)
    {
        $request->validate([
            'network_type' => 'required|string|max:50',
            'api_name' => 'nullable|string|max:255',
            'api_endpoint' => 'required|url|max:500',
            'api_key' => 'required|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'request_method' => 'nullable|string|in:GET,POST,PUT,PATCH',
            'request_headers' => 'nullable|string',
            'request_body_template' => 'nullable|string',
            'response_success_field' => 'nullable|string|max:100',
            'response_data_field' => 'nullable|string|max:100',
            'response_error_field' => 'nullable|string|max:100',
            'timeout_seconds' => 'nullable|integer|min:5|max:300',
            'retry_attempts' => 'nullable|integer|min:0|max:10',
        ]);

        $existing = ApiConfig::where('network_type', $request->input('network_type'))->first();

        $data = [
            'network_type' => $request->input('network_type'),
            'api_name' => $request->input('api_name', $request->input('network_type').' API'),
            'endpoint_url' => $request->input('api_endpoint'),
            'api_key' => $request->input('api_key'),
            'api_secret' => $request->input('api_secret'),
            'is_active' => $request->boolean('is_active', true),
            'request_method' => $request->input('request_method', 'POST'),
            'request_headers' => $request->input('request_headers'),
            'request_body_template' => $request->input('request_body_template'),
            'response_success_field' => $request->input('response_success_field', 'success'),
            'response_data_field' => $request->input('response_data_field', 'data'),
            'response_error_field' => $request->input('response_error_field', 'error'),
            'timeout_seconds' => $request->input('timeout_seconds', 30),
            'retry_attempts' => $request->input('retry_attempts', 3),
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            ApiConfig::create($data);
        }

        return response()->json(['success' => true, 'message' => 'API config updated.']);
    }

    // ─── Config: Data Integration ───────────────────────────

    public function getDataIntegration(Request $request)
    {
        $config = $this->integrationService->getConfig();

        return response()->json(['success' => true, 'config' => $config]);
    }

    public function updateDataIntegration(Request $request)
    {
        $request->validate([
            'data_website_url' => 'nullable|url|max:500',
            'data_website_api_key' => 'nullable|string|max:500',
            'webhook_url' => 'nullable|url|max:500',
            'enabled' => 'nullable|boolean',
        ]);

        foreach ($request->only(['data_website_url', 'data_website_api_key', 'webhook_url', 'enabled']) as $key => $value) {
            if ($value !== null) {
                $this->integrationService->updateConfig($key, $value);
            }
        }

        return response()->json(['success' => true, 'message' => 'Data integration config updated.']);
    }

    // ─── Config: Payment ────────────────────────────────────

    public function getPaymentConfig(Request $request)
    {
        $configs = $this->configService->getAllConfig();

        return response()->json(['success' => true, 'configs' => $configs]);
    }

    public function updatePaymentConfig(Request $request)
    {
        $request->validate([
            'payment_phone_number' => 'nullable|string|max:50',
            'payment_name' => 'nullable|string|max:255',
            'whatsapp_contact' => 'nullable|string|max:50',
            'whatsapp_group_link' => 'nullable|string|max:500',
        ]);

        foreach ($request->only(['payment_phone_number', 'payment_name', 'whatsapp_contact', 'whatsapp_group_link']) as $key => $value) {
            if ($value !== null) {
                $this->configService->updateConfig($key, $value);
            }
        }

        return response()->json(['success' => true, 'message' => 'Payment config updated.']);
    }

    // ─── Config: Paystack Charge ────────────────────────────

    public function getPaystackCharge(Request $request)
    {
        $chargeConfig = $this->chargeManager->getChargeConfig();

        return response()->json(['success' => true, 'charge_config' => $chargeConfig]);
    }

    public function updatePaystackCharge(Request $request)
    {
        $request->validate([
            'charge_amount' => 'required|numeric|min:0',
            'charge_type' => 'required|string|in:fixed,percentage',
        ]);

        $result = $this->chargeManager->updateChargeConfig([
            'charge_amount' => $request->input('charge_amount'),
            'charge_type' => $request->input('charge_type'),
            'admin_id' => $this->admin($request)->id,
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    // ─── Config: Referral ───────────────────────────────────

    public function getReferralConfig(Request $request)
    {
        $configs = ReferralConfig::all();

        return response()->json(['success' => true, 'configs' => $configs]);
    }

    public function updateReferralConfig(Request $request)
    {
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'min_orders_required' => 'required|integer|min:0',
            'max_commission_per_order' => 'nullable|numeric|min:0',
            'is_enabled' => 'nullable|boolean',
        ]);

        $fields = [
            'commission_percentage' => $request->input('commission_percentage'),
            'min_orders_required' => $request->input('min_orders_required'),
            'max_commission_per_order' => $request->input('max_commission_per_order'),
            'is_enabled' => $request->boolean('is_enabled', false),
            'admin_id' => $this->admin($request)->id,
        ];

        foreach ($fields as $key => $value) {
            ReferralConfig::updateOrCreate(
                ['config_key' => $key],
                ['config_value' => (string) $value, 'updated_at' => now()]
            );
        }

        return response()->json(['success' => true, 'message' => 'Referral config updated.']);
    }

    // ─── Config: Low Balance Alert ──────────────────────────

    public function getLowBalanceAlert(Request $request)
    {
        $config = $this->alertService->getConfig();

        return response()->json(['success' => true, 'config' => $config]);
    }

    public function updateLowBalanceAlert(Request $request)
    {
        $request->validate([
            'enabled' => 'nullable|boolean',
            'threshold_amount' => 'required|numeric|min:0',
            'alert_interval_days' => 'required|integer|min:1|max:30',
        ]);

        $this->alertService->updateConfig([
            'enabled' => $request->boolean('enabled', false),
            'threshold_amount' => $request->input('threshold_amount'),
            'alert_interval_days' => $request->input('alert_interval_days'),
        ]);

        return response()->json(['success' => true, 'message' => 'Low balance alert config updated.']);
    }

    // ─── Config: Minimum Topup ──────────────────────────────

    public function getMinimumTopup(Request $request)
    {
        $config = $this->topupManager->getConfig();
        $history = $this->topupManager->getConfigurationHistory(10);

        return response()->json(['success' => true, 'config' => $config, 'history' => $history]);
    }

    public function updateMinimumTopup(Request $request)
    {
        $request->validate(['minimum_amount' => 'required|numeric|min:0']);

        $result = $this->topupManager->updateConfig([
            'minimum_amount' => $request->input('minimum_amount'),
            'admin_id' => $this->admin($request)->id,
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }
}
