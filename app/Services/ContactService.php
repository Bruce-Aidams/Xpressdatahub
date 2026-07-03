<?php

namespace App\Services;

use App\Models\PaymentConfig;

class ContactService
{
    public function getConfig(string $key, string $default = ''): string
    {
        $config = PaymentConfig::where('config_key', $key)->first();

        return $config ? $config->config_value : $default;
    }

    public function getAllContactConfig(): array
    {
        $keys = [
            'payment_phone_number',
            'payment_name',
            'whatsapp_contact',
            'whatsapp_group_link',
            'contact_email',
            'business_address',
            'company_name',
        ];

        $config = [];
        foreach ($keys as $key) {
            $config[$key] = $this->getConfig($key);
        }

        return $config;
    }

    public function getContactPhone(): string
    {
        return $this->getConfig('payment_phone_number', '+233 54 280 1117');
    }

    public function getContactEmail(): string
    {
        return $this->getConfig('contact_email', 'support@ninasdatahub.com');
    }

    public function getWhatsAppContact(): string
    {
        return $this->getConfig('whatsapp_contact', '+233 54 280 1117');
    }

    public function getBusinessAddress(): string
    {
        return $this->getConfig('business_address', 'Ghana');
    }

    public function getCompanyName(): string
    {
        return $this->getConfig('company_name', 'Xpressdatahub');
    }
}
