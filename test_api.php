<?php

use App\Models\ApiConfig;
use App\Models\Order;
use App\Models\PaymentConfig;
use App\Services\ExternalApiService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== Testing ExternalApiService::purchaseData ===\n\n";

// Check latest order
$order = Order::orderBy('id', 'desc')->first();
echo "Latest Order ID: {$order->id}\n";
echo "Phone: {$order->phone_number}\n";
echo "Network: {$order->network_type}\n";
echo "Package: {$order->package_size}\n";
echo "Amount: {$order->amount}\n";
echo "Reference: {$order->order_reference}\n\n";

// Check API config
$config = ApiConfig::where('network_type', $order->network_type)
    ->where('is_active', true)->first();

if (! $config) {
    echo "ERROR: No active API config found for network: {$order->network_type}\n";
    exit(1);
}

echo "API Config Found:\n";
echo "  Endpoint: {$config->endpoint_url}\n";
echo '  API Key: '.substr($config->api_key, 0, 8)."...\n";
echo "  Template: {$config->request_body_template}\n\n";

// Check global api_enabled
$apiEnabled = PaymentConfig::where('config_key', 'api_enabled')->value('config_value');
echo 'api_enabled value: '.var_export($apiEnabled, true)."\n\n";

// Test purchaseData
$svc = new ExternalApiService($order->network_type);
$capacityMb = $svc->convertPackageSize($order->package_size);
echo "Converted package '{$order->package_size}' => {$capacityMb} MB\n\n";

echo "Calling purchaseData...\n";
$result = $svc->purchaseData(
    $order->phone_number,
    $order->network_type,
    $capacityMb,
    'wallet',
    (string) $order->id
);

echo "\n=== RESULT ===\n";
echo 'Success: '.($result['success'] ? 'YES' : 'NO')."\n";
echo 'HTTP Code: '.($result['http_code'] ?? 'N/A')."\n";
echo 'Error: '.($result['error'] ?? 'none')."\n";
echo 'Data: '.json_encode($result['data'] ?? null, JSON_PRETTY_PRINT)."\n";
