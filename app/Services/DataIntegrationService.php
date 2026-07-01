<?php

namespace App\Services;

use App\Models\DataIntegrationConfig;
use Illuminate\Support\Facades\DB;

class DataIntegrationService
{
    public function getConfig(): ?array
    {
        $rows = DataIntegrationConfig::all();
        if ($rows->isEmpty()) {
            return null;
        }
        $config = [];
        foreach ($rows as $row) {
            $config[$row->config_key] = $row->config_value;
        }
        return $config;
    }

    public function isEnabled(): bool
    {
        $enabled = $this->getValue('enabled');
        return $enabled === '1' || $enabled === 'true';
    }

    public function getDataWebsiteUrl(): string
    {
        return $this->getValue('data_website_url');
    }

    public function getApiKey(): string
    {
        return $this->getValue('data_website_api_key');
    }

    public function getWebhookUrl(): string
    {
        return $this->getValue('webhook_url');
    }

    public function getValue(string $key, string $default = ''): string
    {
        $row = DataIntegrationConfig::where('config_key', $key)->first();
        return $row ? $row->config_value : $default;
    }

    public function updateConfig(string $key, $value): bool
    {
        DataIntegrationConfig::updateOrCreate(
            ['config_key' => $key],
            ['config_value' => (string) $value]
        );
        return true;
    }
}
