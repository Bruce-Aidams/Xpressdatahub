<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PackageApiController;
use App\Http\Controllers\Api\PendingPaymentController;
use App\Http\Controllers\Api\WalletApiController;
use App\Http\Controllers\Webhook\DataStatusUpdateWebhookController;
use App\Http\Controllers\Webhook\PaystackWebhookController;
use App\Http\Controllers\Webhook\StatusUpdateWebhookController;
use Illuminate\Support\Facades\Route;

// ─── Public / Agent API ───────────────────────────────────
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/admin/login', [AdminApiController::class, 'login']);

    // Agent routes
    Route::post('/orders', [OrderApiController::class, 'createOrder'])->middleware('api.auth');
    Route::get('/orders', [OrderApiController::class, 'listOrders'])->middleware('api.auth');
    Route::get('/orders/{orderId}', [OrderApiController::class, 'showOrder'])->middleware('api.auth');
    Route::get('/orders/status/{externalTransactionId}', [OrderApiController::class, 'checkOrderStatus'])->middleware('api.auth');
    Route::get('/wallet/balance', [WalletApiController::class, 'balance'])->middleware('api.auth');
    Route::get('/packages', [PackageApiController::class, 'index'])->middleware('api.auth');
    Route::get('/payments/pending', [PendingPaymentController::class, 'index'])->middleware('api.auth');
});

// ─── Admin API ────────────────────────────────────────────
Route::prefix('v1/admin')->middleware('admin.api')->group(function () {

    // Profile
    Route::get('/profile', [AdminApiController::class, 'profile']);
    Route::put('/profile', [AdminApiController::class, 'updateProfile']);
    Route::put('/password', [AdminApiController::class, 'changePassword']);

    // Dashboard
    Route::get('/dashboard', [AdminApiController::class, 'dashboard']);

    // Agents CRUD
    Route::get('/agents', [AdminApiController::class, 'listAgents']);
    Route::get('/agents/{agent}', [AdminApiController::class, 'showAgent']);
    Route::post('/agents', [AdminApiController::class, 'createAgent']);
    Route::put('/agents/{agent}', [AdminApiController::class, 'updateAgent']);
    Route::delete('/agents/{agent}', [AdminApiController::class, 'deleteAgent']);
    Route::put('/agents/{agent}/status', [AdminApiController::class, 'updateAgentStatus']);

    // Orders
    Route::get('/orders', [AdminApiController::class, 'listOrders']);
    Route::get('/orders/all', [AdminApiController::class, 'allOrders']);
    Route::get('/orders/{order}', [AdminApiController::class, 'showOrder']);
    Route::put('/orders/{order}/status', [AdminApiController::class, 'updateOrderStatus']);

    // Shops
    Route::get('/shops', [AdminApiController::class, 'listShops']);
    Route::get('/shops/{shop}', [AdminApiController::class, 'showShop']);
    Route::put('/shops/{shop}/status', [AdminApiController::class, 'updateShopStatus']);

    // Shop Orders
    Route::get('/shop-orders', [AdminApiController::class, 'listShopOrders']);
    Route::post('/shop-orders/verify', [AdminApiController::class, 'verifyShopOrder']);

    // Shop Withdrawals
    Route::get('/shop-withdrawals', [AdminApiController::class, 'listShopWithdrawals']);
    Route::post('/shop-withdrawals/{shopWithdrawal}/approve', [AdminApiController::class, 'approveWithdrawal']);
    Route::post('/shop-withdrawals/{shopWithdrawal}/reject', [AdminApiController::class, 'rejectWithdrawal']);

    // Pricing
    Route::get('/pricing', [AdminApiController::class, 'listPricing']);
    Route::post('/pricing', [AdminApiController::class, 'createPricing']);
    Route::put('/pricing/{customPricing}', [AdminApiController::class, 'updatePricing']);
    Route::put('/pricing/{customPricing}/toggle', [AdminApiController::class, 'togglePricing']);
    Route::delete('/pricing/{customPricing}', [AdminApiController::class, 'deletePricing']);

    // Accounts
    Route::get('/accounts', [AdminApiController::class, 'listAccounts']);
    Route::put('/accounts/{agent}/status', [AdminApiController::class, 'updateAccountStatus']);

    // Banners
    Route::get('/banners', [AdminApiController::class, 'listBanners']);
    Route::post('/banners', [AdminApiController::class, 'createBanner']);
    Route::put('/banners/{bannerNotification}', [AdminApiController::class, 'updateBanner']);
    Route::delete('/banners/{bannerNotification}', [AdminApiController::class, 'deleteBanner']);

    // API Keys
    Route::get('/api-keys', [AdminApiController::class, 'listApiKeys']);
    Route::post('/api-keys', [AdminApiController::class, 'createApiKey']);
    Route::put('/api-keys/{apiKey}', [AdminApiController::class, 'updateApiKey']);
    Route::delete('/api-keys/{apiKey}', [AdminApiController::class, 'deleteApiKey']);

    // Analytics
    Route::get('/analytics', [AdminApiController::class, 'analytics']);

    // Balance History
    Route::get('/balance-history', [AdminApiController::class, 'listBalanceHistory']);

    // User Activity
    Route::get('/user-activity', [AdminApiController::class, 'listUserActivity']);

    // Notifications
    Route::get('/notifications', [AdminApiController::class, 'listNotifications']);
    Route::post('/notifications/send', [AdminApiController::class, 'sendNotification']);

    // Config: API
    Route::get('/config/api', [AdminApiController::class, 'getApiConfig']);
    Route::put('/config/api', [AdminApiController::class, 'updateApiConfig']);

    // Config: Data Integration
    Route::get('/config/data-integration', [AdminApiController::class, 'getDataIntegration']);
    Route::put('/config/data-integration', [AdminApiController::class, 'updateDataIntegration']);

    // Config: Payment
    Route::get('/config/payment', [AdminApiController::class, 'getPaymentConfig']);
    Route::put('/config/payment', [AdminApiController::class, 'updatePaymentConfig']);

    // Config: Paystack Charge
    Route::get('/config/paystack-charge', [AdminApiController::class, 'getPaystackCharge']);
    Route::put('/config/paystack-charge', [AdminApiController::class, 'updatePaystackCharge']);

    // Config: Referral
    Route::get('/config/referral', [AdminApiController::class, 'getReferralConfig']);
    Route::put('/config/referral', [AdminApiController::class, 'updateReferralConfig']);

    // Config: Low Balance Alert
    Route::get('/config/low-balance-alert', [AdminApiController::class, 'getLowBalanceAlert']);
    Route::put('/config/low-balance-alert', [AdminApiController::class, 'updateLowBalanceAlert']);

    // Config: Minimum Topup
    Route::get('/config/minimum-topup', [AdminApiController::class, 'getMinimumTopup']);
    Route::put('/config/minimum-topup', [AdminApiController::class, 'updateMinimumTopup']);
});

// ─── Webhooks ──────────────────────────────────────────────
Route::prefix('v1/webhook')->group(function () {
    Route::post('/paystack', [PaystackWebhookController::class, 'handle']);
    Route::post('/status-update', [StatusUpdateWebhookController::class, 'handle']);
    Route::post('/data-status-update', [DataStatusUpdateWebhookController::class, 'handle']);
});
