<?php

namespace App\Services;

use App\Models\PaymentConfig;

class PaymentConfigService
{
    public function getConfig(string $key, string $default = ''): string
    {
        $config = PaymentConfig::where('config_key', $key)->first();

        return $config ? $config->config_value : $default;
    }

    public function getAllConfig(): array
    {
        $configs = PaymentConfig::pluck('config_value', 'config_key')->toArray();

        return $configs;
    }

    public function getPaymentPhone(): string
    {
        return $this->getConfig('payment_phone_number', '+233 54 280 1117');
    }

    public function getPaymentName(): string
    {
        return $this->getConfig('payment_name', 'David Agyei Opoku');
    }

    public function getWhatsAppContact(): string
    {
        return $this->getConfig('whatsapp_contact', '233542801117');
    }

    public function getWhatsAppGroupLink(): string
    {
        return $this->getConfig(
            'whatsapp_group_link',
            'https://chat.whatsapp.com/IXIOy4QBOPdKK87sqpgblY?mode=ems_copy_t'
        );
    }

    public function getPaymentInstructions(): string
    {
        $phone = $this->getPaymentPhone();
        $name = $this->getPaymentName();

        return "Pay to <span class=\"font-bold bg-accent-500 text-white px-2 py-1 rounded\">{$phone} ({$name})</span>";
    }

    public function updateConfig(string $key, string $value): bool
    {
        $existing = PaymentConfig::where('config_key', $key)->first();

        if ($existing) {
            $existing->update(['config_value' => $value]);

            return true;
        }

        PaymentConfig::create([
            'config_key' => $key,
            'config_value' => $value,
            'description' => ucwords(str_replace('_', ' ', $key)).' configuration',
        ]);

        return true;
    }
}
