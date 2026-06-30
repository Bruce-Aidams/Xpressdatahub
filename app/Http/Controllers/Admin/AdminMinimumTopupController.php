<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MinimumTopupManager;
use Illuminate\Http\Request;

class AdminMinimumTopupController extends Controller
{
    public function __construct(
        private MinimumTopupManager $topupManager
    ) {}

    public function index()
    {
        $config = $this->topupManager->getConfig();
        $history = $this->topupManager->getConfigurationHistory(10);

        return view('admin.config.minimum-topup', compact('config', 'history'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'minimum_amount' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->topupManager->updateConfig([
                'minimum_amount' => $request->input('minimum_amount'),
                'admin_id' => session('admin_id'),
            ]);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update minimum top-up configuration.');
        }
    }
}
