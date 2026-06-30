<?php

namespace App\Services;

use App\Models\DataIntegrationConfig;
use Illuminate\Support\Facades\DB;

class DataIntegrationService
{
    public function getConfig(): ?array
    {
        $config = DataIntegrationConfig::first();
        return $config ? $config->toArray() : null;
    }

    public function isEnabled(): bool
    {
        $config = $this->getConfig();
        return $config && !empty($config['enabled']) && !empty($config['data_website_api_key']);
    }

    public function getDataWebsiteUrl(): string
    {
        $config = $this->getConfig();
        return $config ? rtrim($config['data_website_url'] ?? '', '/') : '';
    }

    public function getApiKey(): string
    {
        $config = $this->getConfig();
        return $config ? $config['data_website_api_key'] ?? '' : '';
    }

    public function getWebhookUrl(): string
    {
        $config = $this->getConfig();
        return $config ? $config['webhook_url'] ?? '' : '';
    }

    public function updateConfig(string $key, string $value): bool
    {
        $config = DataIntegrationConfig::firstOrCreate(
            ['id' => 1],
            [$key => $value]
        );

        $config->update([$key => $value]);
        return true;
    }
}
