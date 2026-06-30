<?php

namespace App\Services;

use App\Models\MinimumTopupConfig;
use Illuminate\Support\Facades\DB;

class MinimumTopupManager
{
    public function getConfig(): array
    {
        $config = MinimumTopupConfig::where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if (!$config) {
            return [
                'id' => 0,
                'minimum_amount' => 10.00,
                'is_active' => false,
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
        $adminId = $data['admin_id'] ?? 0;

        if ($minimumAmount < 0) {
            return ['success' => false, 'message' => 'Invalid minimum amount'];
        }

        MinimumTopupConfig::where('is_active', true)->update(['is_active' => false]);

        MinimumTopupConfig::create([
            'minimum_amount' => $minimumAmount,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        return ['success' => true, 'message' => 'Minimum top-up configuration updated successfully'];
    }

    public function validateTopupAmount(float $amount): array
    {
        $config = $this->getConfig();

        if (!$config['is_active']) {
            return ['valid' => true, 'message' => 'Minimum top-up validation is disabled'];
        }

        $minimumAmount = floatval($config['minimum_amount']);

        if ($amount < $minimumAmount) {
            return [
                'valid' => false,
                'message' => 'Minimum top-up amount is GH₵ ' . number_format($minimumAmount, 2)
                    . '. Please enter an amount of GH₵ ' . number_format($minimumAmount, 2) . ' or more.',
                'minimum_amount' => $minimumAmount,
            ];
        }

        return ['valid' => true, 'message' => 'Amount is valid'];
    }

    public function getMinimumAmount(): float
    {
        $config = $this->getConfig();
        return $config['is_active'] ? floatval($config['minimum_amount']) : 0;
    }

    public function isMinimumTopupEnabled(): bool
    {
        $config = $this->getConfig();
        return (bool) $config['is_active'];
    }

    public function getConfigurationHistory(int $limit = 10): array
    {
        return MinimumTopupConfig::orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
