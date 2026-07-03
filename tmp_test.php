<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Agent;
use App\Services\UserLoginTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

try {
    // create fake agent
    $agent = Agent::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser' . time(),
        'email' => 'test' . time() . '@example.com',
        'password_hash' => Hash::make('password'),
        'role' => 'agent',
        'status' => 'active'
    ]);

    $server = ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_USER_AGENT' => 'Testing Browser'];
    $request = Request::create('/login', 'POST', [], [], [], $server);

    $tracker = app(UserLoginTracker::class);
    $tracker->logLogin($agent->id, $request->ip(), $request->userAgent());
    
    $agent->update(['last_login_ip' => $request->ip()]);
    
    echo "Success";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
