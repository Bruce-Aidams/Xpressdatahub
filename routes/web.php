<?php

use App\Http\Controllers\Admin\AdminAccountManagementController;
use App\Http\Controllers\Admin\AdminAgentController;
use App\Http\Controllers\Admin\AdminAllOrderController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminApiConfigController;
use App\Http\Controllers\Admin\AdminApiKeyController;
use App\Http\Controllers\Admin\AdminBalanceHistoryController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDataIntegrationController;
use App\Http\Controllers\Admin\AdminLowBalanceAlertController;
use App\Http\Controllers\Admin\AdminMinimumTopupController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPasswordController;
use App\Http\Controllers\Admin\AdminPaymentConfigController;
use App\Http\Controllers\Admin\AdminPaystackChargeController;
use App\Http\Controllers\Admin\AdminPricingController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminReferralConfigController;
use App\Http\Controllers\Admin\AdminShopController;
use App\Http\Controllers\Admin\AdminShopOrderController;
use App\Http\Controllers\Admin\AdminShopWithdrawalController;
use App\Http\Controllers\Admin\AdminUserActivityController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\GuestShopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\BulkOrderController;

use App\Http\Controllers\User\GuestCallbackController;
use App\Http\Controllers\User\UserApiKeyController;
use App\Http\Controllers\User\UserBalanceHistoryController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\UserDataController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\UserPasswordController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserReferralController;
use App\Http\Controllers\User\UserShopController;
use App\Http\Controllers\User\UserShopProfitController;
use App\Http\Controllers\User\UserWalletController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Guest Paystack Callback
Route::get('/guest/callback', [GuestCallbackController::class, 'handleCallback'])->name('guest.callback');
Route::get('/guest/order/success', function () {
    return view('guest.success');
})->name('guest.order.success');

// Guest Shop routes — specific routes MUST come before parameterized routes
Route::get('/shop/callback', [GuestShopController::class, 'callback'])->name('shop.order.callback');
Route::get('/shop/order/error', [GuestShopController::class, 'error'])->name('shop.order.error');
Route::get('/shop/order/{orderId}/confirm', [GuestShopController::class, 'confirm'])->name('shop.order.confirm');
Route::get('/shop/{slug}', [GuestShopController::class, 'show'])->name('shop.show');
Route::post('/shop/{slug}/order', [GuestShopController::class, 'order'])->name('shop.order');

// Auth routes - Guest
Route::middleware('guest')->group(function () {
    $adminPath = config('app.admin_path');

    // Admin Login
    Route::get('/'.$adminPath.'/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/'.$adminPath.'/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

    // User Login
    Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserLoginController::class, 'login']);

    // Guest Login
    Route::get('/guest-login', [UserLoginController::class, 'guestLogin'])->name('guest.login');

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Pending Approval
    Route::get('/pending-approval', function () {
        return view('auth.pending-approval');
    })->name('pending.approval');

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset.post');
});

// Admin Logout
$adminPath = config('app.admin_path');
Route::post('/'.$adminPath.'/logout', [AdminLoginController::class, 'logout'])->name('admin.logout')->middleware('admin.auth');

// User Logout
Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout')->middleware('user.auth');

// Admin Panel
Route::prefix(config('app.admin_path'))->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk/status', [AdminOrderController::class, 'bulkStatusUpdate'])->name('orders.bulk.status');
    Route::post('/orders/bulk/delete', [AdminOrderController::class, 'bulkDelete'])->name('orders.bulk.delete');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/all-orders', [AdminAllOrderController::class, 'index'])->name('orders.all');

    // Agents
    Route::resource('agents', AdminAgentController::class)->except(['edit', 'create']);
    Route::put('/agents/{agent}/status', [AdminAgentController::class, 'updateStatus'])->name('agents.toggle-status');
    Route::put('/agents/{agent}/reset-password', [AdminAgentController::class, 'resetPassword'])->name('agents.reset-password');
    Route::post('/agents/{agent}/send-reset-link', [AdminAgentController::class, 'sendResetLink'])->name('agents.send-reset-link');
    Route::get('/agents/pending/approvals', [AdminAgentController::class, 'pendingApprovals'])->name('agents.pending-approvals');
    Route::post('/agents/{agent}/approve', [AdminAgentController::class, 'approve'])->name('agents.approve');
    Route::post('/agents/{agent}/reject', [AdminAgentController::class, 'reject'])->name('agents.reject');
    Route::post('/agents/{agent}/make-admin', [AdminAgentController::class, 'makeAdmin'])->name('agents.make-admin');

    // Pricing
    Route::get('/pricing', [AdminPricingController::class, 'index'])->name('pricing.index');
    Route::post('/pricing', [AdminPricingController::class, 'store'])->name('pricing.store');
    Route::put('/pricing/{customPricing}', [AdminPricingController::class, 'update'])->name('pricing.update');
    Route::delete('/pricing/{customPricing}', [AdminPricingController::class, 'destroy'])->name('pricing.destroy');
    Route::patch('/pricing/{customPricing}/toggle', [AdminPricingController::class, 'toggle'])->name('pricing.toggle');
    Route::post('/pricing/bulk/toggle', [AdminPricingController::class, 'bulkToggle'])->name('pricing.bulk.toggle');
    Route::delete('/pricing/bulk/delete', [AdminPricingController::class, 'bulkDelete'])->name('pricing.bulk.delete');

    // Config - Payment
    Route::get('/payment-config', [AdminPaymentConfigController::class, 'index'])->name('payment-config');
    Route::put('/payment-config', [AdminPaymentConfigController::class, 'update'])->name('payment-config.update');
    Route::get('/payment-config', [AdminPaymentConfigController::class, 'index'])->name('config.payment');
    Route::put('/payment-config', [AdminPaymentConfigController::class, 'update'])->name('config.payment.update');

    // Manual Topups
    Route::get('/manual-topups', [\App\Http\Controllers\Admin\AdminManualTopupController::class, 'index'])->name('manual-topups.index');
    Route::post('/manual-topups/{payment}/approve', [\App\Http\Controllers\Admin\AdminManualTopupController::class, 'approve'])->name('manual-topups.approve');
    Route::post('/manual-topups/{payment}/reject', [\App\Http\Controllers\Admin\AdminManualTopupController::class, 'reject'])->name('manual-topups.reject');

    // Config - Paystack Charge
    Route::get('/paystack-charge', [AdminPaystackChargeController::class, 'index'])->name('paystack-charge');
    Route::put('/paystack-charge', [AdminPaystackChargeController::class, 'update'])->name('paystack-charge.update');
    Route::get('/paystack-charge', [AdminPaystackChargeController::class, 'index'])->name('config.paystack-charge');
    Route::put('/paystack-charge', [AdminPaystackChargeController::class, 'update'])->name('config.paystack-charge.update');

    // Config - Minimum Topup
    Route::get('/minimum-topup', [AdminMinimumTopupController::class, 'index'])->name('minimum-topup');
    Route::put('/minimum-topup', [AdminMinimumTopupController::class, 'update'])->name('minimum-topup.update');
    Route::get('/minimum-topup', [AdminMinimumTopupController::class, 'index'])->name('config.minimum-topup');
    Route::put('/minimum-topup', [AdminMinimumTopupController::class, 'update'])->name('config.minimum-topup.update');

    // Config - Referral
    Route::get('/referral-config', [AdminReferralConfigController::class, 'index'])->name('referral-config');
    Route::put('/referral-config', [AdminReferralConfigController::class, 'update'])->name('referral-config.update');
    Route::get('/referral-config', [AdminReferralConfigController::class, 'index'])->name('config.referral');
    Route::put('/referral-config', [AdminReferralConfigController::class, 'update'])->name('config.referral.update');

    // API Config
    Route::get('/api-config', [AdminApiConfigController::class, 'index'])->name('api-config');
    Route::post('/api-config', [AdminApiConfigController::class, 'store'])->name('api-config.store');
    Route::post('/api-config/{apiConfig}/toggle', [AdminApiConfigController::class, 'toggle'])->name('api-config.toggle');
    Route::post('/api-config/{apiConfig}/test', [AdminApiConfigController::class, 'testConnection'])->name('api-config.test');
    Route::delete('/api-config/{apiConfig}', [AdminApiConfigController::class, 'destroy'])->name('api-config.destroy');

    // API Keys
    Route::get('/api-keys', [AdminApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [AdminApiKeyController::class, 'store'])->name('api-keys.store');
    Route::put('/api-keys/{apiKey}', [AdminApiKeyController::class, 'update'])->name('api-keys.update');
    Route::delete('/api-keys/{apiKey}', [AdminApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::delete('/api-keys/{apiKey}', [AdminApiKeyController::class, 'destroy'])->name('api-keys.revoke');

    // Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [AdminNotificationController::class, 'send'])->name('notifications.send');

    // Banners
    Route::get('/banners', [AdminBannerController::class, 'index'])->name('banners.index');
    Route::post('/banners', [AdminBannerController::class, 'store'])->name('banners.store');
    Route::post('/banners/{bannerNotification}/toggle', [AdminBannerController::class, 'toggle'])->name('banners.toggle');
    Route::put('/banners/{bannerNotification}', [AdminBannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{bannerNotification}', [AdminBannerController::class, 'destroy'])->name('banners.destroy');

    // Account Management
    Route::get('/accounts', [AdminAccountManagementController::class, 'index'])->name('accounts');
    Route::put('/accounts/{agent}/status', [AdminAccountManagementController::class, 'updateStatus'])->name('accounts.status');
    Route::put('/accounts/{agent}/status', [AdminAccountManagementController::class, 'updateStatus'])->name('accounts.toggle');
    Route::get('/accounts', [AdminAccountManagementController::class, 'index'])->name('accounts.index');
    Route::post('/accounts/bulk/credit', [AdminAccountManagementController::class, 'bulkCredit'])->name('accounts.bulk.credit');
    Route::post('/accounts/bulk/debit', [AdminAccountManagementController::class, 'bulkDebit'])->name('accounts.bulk.debit');
    Route::post('/accounts/bulk/status', [AdminAccountManagementController::class, 'bulkStatus'])->name('accounts.bulk.status');

    // Balance History
    Route::get('/balance-history', [AdminBalanceHistoryController::class, 'index'])->name('balance-history');
    Route::get('/balance-history', [AdminBalanceHistoryController::class, 'index'])->name('balance-history.index');

    // Shops
    Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
    Route::get('/shops/{shop}', [AdminShopController::class, 'show'])->name('shops.show');
    Route::put('/shops/{shop}/status', [AdminShopController::class, 'updateStatus'])->name('shops.status');
    Route::delete('/shops/{shop}', [AdminShopController::class, 'destroy'])->name('shops.destroy');
    Route::post('/shops/bulk/status', [AdminShopController::class, 'bulkStatus'])->name('shops.bulk.status');
    Route::delete('/shops/bulk/delete', [AdminShopController::class, 'bulkDelete'])->name('shops.bulk.delete');
    Route::get('/shop-orders', [AdminShopOrderController::class, 'index'])->name('shop-orders');
    Route::get('/shop-orders', [AdminShopOrderController::class, 'index'])->name('shop-orders.index');
    Route::post('/shop-orders/verify', [AdminShopOrderController::class, 'verifyOrder'])->name('shop-orders.verify');
    Route::get('/shop-withdrawals', [AdminShopWithdrawalController::class, 'index'])->name('shop-withdrawals');
    Route::get('/shop-withdrawals', [AdminShopWithdrawalController::class, 'index'])->name('shop-withdrawals.index');
    Route::post('/shop-withdrawals/{shopWithdrawal}/approve', [AdminShopWithdrawalController::class, 'approve'])->name('shop-withdrawals.approve');
    Route::post('/shop-withdrawals/{shopWithdrawal}/reject', [AdminShopWithdrawalController::class, 'reject'])->name('shop-withdrawals.reject');
    Route::post('/shop-withdrawals/{shopWithdrawal}/complete', [AdminShopWithdrawalController::class, 'complete'])->name('shop-withdrawals.complete');
    Route::delete('/shops/{shop}', [AdminShopController::class, 'destroy'])->name('shops.destroy');
    Route::put('/shop-pricing/{shopPricing}', [AdminShopController::class, 'updatePricing'])->name('shops.pricing.update');

    // Profile
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.index');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [AdminPasswordController::class, 'update'])->name('password.update');

    // Analytics
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

    // User Activity
    Route::get('/user-activity', [AdminUserActivityController::class, 'index'])->name('user-activity');
    Route::get('/user-activity', [AdminUserActivityController::class, 'index'])->name('user-activity.index');

    // Data Integration
    Route::get('/data-integration', [AdminDataIntegrationController::class, 'index'])->name('data-integration');
    Route::get('/data-integration', [AdminDataIntegrationController::class, 'index'])->name('config.data-integration');
    Route::put('/data-integration', [AdminDataIntegrationController::class, 'update'])->name('data-integration.update');
    Route::put('/data-integration', [AdminDataIntegrationController::class, 'update'])->name('config.data-integration.update');

    // Low Balance Alert
    Route::get('/low-balance-alert', [AdminLowBalanceAlertController::class, 'index'])->name('config.low-balance-alert');
    Route::put('/low-balance-alert', [AdminLowBalanceAlertController::class, 'update'])->name('config.low-balance-alert.update');
});

// User Dashboard
Route::prefix('user')->name('user.')->middleware('user.auth')->group(function () {
    Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.index');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [UserOrderController::class, 'index'])->name('orders');
    Route::get('/today-orders', [UserOrderController::class, 'todayOrders'])->name('orders.today');
    Route::get('/balance-history', [UserBalanceHistoryController::class, 'index'])->name('balance-history.index');
    Route::get('/api-keys', [UserApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [UserApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [UserApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::get('/password', [UserPasswordController::class, 'showForm'])->name('password.change');
    Route::put('/password', [UserPasswordController::class, 'update'])->name('password.update');
    Route::get('/referrals', [UserReferralController::class, 'index'])->name('referrals.index');
    Route::get('/shop', [UserShopController::class, 'index'])->name('shop.index');
    Route::put('/shop', [UserShopController::class, 'update'])->name('shop.update');
    Route::get('/shop/pricing', [UserShopController::class, 'pricing'])->name('shop.pricing');
    Route::get('/shop-profits', [UserShopProfitController::class, 'index'])->name('shop-profits.index');
    Route::post('/shop-profits/withdraw', [UserShopProfitController::class, 'index'])->name('shop-profits.withdraw');

    // Wallet Topup
    Route::get('/wallet/topup', [UserWalletController::class, 'topupForm'])->name('wallet.topup');
    Route::post('/wallet/topup', [UserWalletController::class, 'initializeTopup'])->name('wallet.topup.init');
    Route::post('/wallet/manual-topup', [UserWalletController::class, 'manualTopup'])->name('wallet.topup.manual');
    Route::get('/wallet/callback', [UserWalletController::class, 'callback'])->name('wallet.callback');

    // Buy Data
    Route::get('/buy-data', [UserDataController::class, 'index'])->name('buy-data');
    Route::post('/buy-data', [UserDataController::class, 'store'])->name('buy-data.store');

    // Notifications
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::match(['get', 'post'], '/notifications/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Bulk Orders
    Route::post('/bulk-orders', [BulkOrderController::class, 'store'])->name('bulk-orders.store');

    // Guest Logout
    Route::get('/guest/logout', function () {
        session()->forget(['guest_mode', 'guest临时_id', 'user_login_time']);
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Guest session ended.');
    })->name('guest.logout');
});
