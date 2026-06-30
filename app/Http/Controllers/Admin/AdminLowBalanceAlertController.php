<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LowBalanceAlertService;
use Illuminate\Http\Request;

class AdminLowBalanceAlertController extends Controller
{
    public function __construct(
        private LowBalanceAlertService $alertService
    ) {}

    public function index()
    {
        $config = $this->alertService->getConfig();

        return view('admin.config.low-balance-alert', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'enabled' => 'nullable|boolean',
            'threshold_amount' => 'required|numeric|min:0',
            'alert_interval_days' => 'required|integer|min:1|max:30',
        ]);

        try {
            $this->alertService->updateConfig([
                'enabled' => $request->boolean('enabled', false),
                'threshold_amount' => $request->input('threshold_amount'),
                'alert_interval_days' => $request->input('alert_interval_days'),
            ]);

            return redirect()->back()
                ->with('success', 'Low balance alert configuration updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update low balance alert configuration.');
        }
    }
}
