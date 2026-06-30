<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataIntegrationService;
use Illuminate\Http\Request;

class AdminDataIntegrationController extends Controller
{
    public function __construct(
        private DataIntegrationService $integrationService
    ) {}

    public function index()
    {
        $config = $this->integrationService->getConfig();

        return view('admin.config.data-integration', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'data_website_url' => 'nullable|url|max:500',
            'data_website_api_key' => 'nullable|string|max:500',
            'webhook_url' => 'nullable|url|max:500',
            'enabled' => 'nullable|boolean',
        ]);

        try {
            $fields = $request->only([
                'data_website_url', 'data_website_api_key', 'webhook_url', 'enabled',
            ]);

            foreach ($fields as $key => $value) {
                if ($value !== null) {
                    $this->integrationService->updateConfig($key, $value);
                }
            }

            return redirect()->back()
                ->with('success', 'Data integration configuration updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update data integration configuration.');
        }
    }
}
