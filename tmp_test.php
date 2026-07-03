<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\CustomPricing;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

// Create a request and inject admin session manually
$server = [
    'HTTP_HOST' => 'localhost',
    'REQUEST_URI' => '/admin/pricing',
    'REQUEST_METHOD' => 'GET',
];
$request = Request::create('/admin/pricing', 'GET', [], [], [], $server);

// Manually handle through kernel
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// We need to fake auth. Let's check if there's a way
// Actually, let's just render the blade template directly
$view = View::make('admin.pricing.index', [
    'pricingRules' => CustomPricing::orderBy('package_size_gb')->paginate(20),
]);

echo $view->render();
