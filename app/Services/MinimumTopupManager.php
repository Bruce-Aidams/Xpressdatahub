<?php

namespace App\Services;

use App\Models\MinimumTopupConfig;

class MinimumTopupManager
{
    public function getConfig(): array
    {
        $config = MinimumTopupConfig::where('is_enabled', true)
            ->orderByDesc('id')
            ->first();

        if (! $config) {
            return [
                'id' => 0,
                'minimum_amount' => 10.00,
                'maximum_amount' => null,
                'is_enabled' => false,
                'created_by' => 0,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        return $config->toArray();
    }

    public function updateConfig(array $data): array
    {
        $minimumAmount = floatval($data['minimum_amount'] ?? 0);
        $maximumAmount = ! empty($data['maximum_amount']) ? floatval($data['maximum_amount']) : null;
        $adminId = $data['admin_id'] ?? 0;

        if ($minimumAmount < 0) {
            return ['success' => false, 'message' => 'Invalid minimum amount'];
        }

        if ($maximumAmount !== null && $maximumAmount < $minimumAmount) {
            return ['success' => false, 'message' => 'Maximum amount must be greater than or equal to minimum amount'];
        }

        MinimumTopupConfig::where('is_enabled', true)->update(['is_enabled' => false]);

        MinimumTopupConfig::create([
            'minimum_amount' => $minimumAmount,
            'maximum_amount' => $maximumAmount,
            'is_enabled' => true,
            'created_by' => $adminId,
        ]);

        return ['success' => true, 'message' => 'Minimum top-up configuration updated successfully'];
    }

    public function validateTopupAmount(float $amount): array
    {
        $config = $this->getConfig();

        if (! $config['is_enabled']) {
            return ['valid' => true, 'message' => 'Minimum top-up validation is disabled'];
        }

        $minimumAmount = floatval($config['minimum_amount']);
        $maximumAmount = ! empty($config['maximum_amount']) ? floatval($config['maximum_amount']) : null;

        if ($amount < $minimumAmount) {
            return [
                'valid' => false,
                'message' => 'Minimum top-up amount is GH\u20B5 '.number_format($minimumAmount, 2)
                    .'. Please enter an amount of GH\u20B5 '.number_format($minimumAmount, 2).' or more.',
                'minimum_amount' => $minimumAmount,
            ];
        }

        if ($maximumAmount !== null && $amount > $maximumAmount) {
            return [
                'valid' => false,
                'message' => 'Maximum top-up amount is GH\u20B5 '.number_format($maximumAmount, 2)
                    .'. Please enter an amount of GH\u20B5 '.number_format($maximumAmount, 2).' or less.',
                'maximum_amount' => $maximumAmount,
            ];
        }

        return ['valid' => true, 'message' => 'Amount is valid'];
    }

    public function getMinimumAmount(): float
    {
        $config = $this->getConfig();

        return $config['is_enabled'] ? floatval($config['minimum_amount']) : 0;
    }

    public function getMaximumAmount(): ?float
    {
        $config = $this->getConfig();
        if (! $config['is_enabled'] || empty($config['maximum_amount'])) {
            return null;
        }

        return floatval($config['maximum_amount']);
    }

    public function isMinimumTopupEnabled(): bool
    {
        $config = $this->getConfig();

        return (bool) $config['is_enabled'];
    }

    public function getConfigurationHistory(int $limit = 10): array
    {
        return MinimumTopupConfig::orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
