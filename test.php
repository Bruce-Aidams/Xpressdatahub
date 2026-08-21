<?php

use App\Models\Agent;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$agent = Agent::first();
if ($agent) {
    echo 'Initial balance: '.$agent->balance.PHP_EOL;
    $orderService = app(OrderService::class);
    $result = $orderService->createOrder([
        'agent_id' => $agent->id,
        'network_type' => 'MTN',
        'package_size' => '1 GB',
        'amount' => 10.00,
        'payment_method' => 'wallet',
    ]);
    if ($result['success']) {
        $agent->decrement('balance', 10.00);
        echo 'Balance after purchase: '.$agent->fresh()->balance.PHP_EOL;

        $orderService->updateOrderStatus($result['order']['id'], 'failed');
        echo 'Balance after failure: '.$agent->fresh()->balance.PHP_EOL;

        $order = Order::find($result['order']['id']);
        echo 'Order is_refunded: '.($order->is_refunded ? 'true' : 'false').PHP_EOL;
    }
}
