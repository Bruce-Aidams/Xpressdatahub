<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentConfigService;
use Illuminate\Http\Request;

class AdminMaintenanceController extends Controller
{
    public function index(PaymentConfigService $service)
    {
        $isMaintenance = $service->getConfig('maintenance_mode', 'false') === 'true';
        return view('admin.config.maintenance', compact('isMaintenance'));
    }

    public function toggle(Request $request, PaymentConfigService $service)
    {
        $current = $service->getConfig('maintenance_mode', 'false') === 'true';
        $new = $current ? 'false' : 'true';
        
        $service->updateConfig('maintenance_mode', $new);
        
        $status = $new === 'true' ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Maintenance mode has been $status.");
    }
}
