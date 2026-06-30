<?php

namespace App\Services;

use App\Models\PaystackTopupCharge;
use Illuminate\Support\Facades\DB;

class PaystackChargeManager
{
    public function getChargeConfig(): array
    {
        $config = PaystackTopupCharge::where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if (!$config) {
            return [
                'id' => 0,
                'charge_amount' => 0.00,
                'charge_type' => 'fixed',
                'is_active' => false,
                'created_by' => 0,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        return $config->toArray();
    }

    public function updateChargeConfig(array $data): array
    {
        $chargeAmount = floatval($data['charge_amount'] ?? 0);
        $chargeType = $data['charge_type'] ?? 'fixed';
        $adminId = $data['admin_id'] ?? 0;

        if ($chargeAmount < 0) {
            return ['success' => false, 'message' => 'Invalid charge amount'];
        }

        if (!in_array($chargeType, ['fixed', 'percentage'])) {
            return ['success' => false, 'message' => 'Invalid charge type'];
        }

        if ($chargeType === 'percentage' && $chargeAmount > 100) {
            return ['success' => false, 'message' => 'Percentage charge cannot exceed 100%'];
        }

        PaystackTopupCharge::where('is_active', true)->update(['is_active' => false]);

        PaystackTopupCharge::create([
            'charge_amount' => $chargeAmount,
            'charge_type' => $chargeType,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        return ['success' => true, 'message' => 'Paystack charge configuration updated successfully'];
    }

    public function calculateChargedAmount(float $baseAmount): array
    {
        $config = $this->getChargeConfig();

        if (!$config['is_active']) {
            return [
                'base_amount' => $baseAmount,
                'charge_amount' => 0.00,
                'total_amount' => $baseAmount,
                'charge_type' => 'none',
            ];
        }

        $chargeAmount = floatval($config['charge_amount']);
        $chargeType = $config['charge_type'];
        $calculatedCharge = 0.00;

        if ($chargeType === 'fixed') {
            $calculatedCharge = $chargeAmount;
        } elseif ($chargeType === 'percentage') {
            $calculatedCharge = ($baseAmount * $chargeAmount) / 100;
        }

        $totalAmount = $baseAmount + $calculatedCharge;

        return [
            'base_amount' => $baseAmount,
            'charge_amount' => round($calculatedCharge, 2),
            'total_amount' => round($totalAmount, 2),
            'charge_type' => $chargeType,
        ];
    }

    public function isChargeEnabled(): bool
    {
        $config = $this->getChargeConfig();
        return (bool) $config['is_active'];
    }

    public function getChargeDescription(): string
    {
        $config = $this->getChargeConfig();

        if (!$config['is_active']) {
            return 'No charges applied';
        }

        $amount = floatval($config['charge_amount']);
        $type = $config['charge_type'];

        if ($type === 'fixed') {
            return 'GH₵ ' . number_format($amount, 2) . ' flat charge';
        }

        return number_format($amount, 2) . '% charge';
    }

    public function disableCharge(): array
    {
        PaystackTopupCharge::where('is_active', true)->update(['is_active' => false]);
        return ['success' => true, 'message' => 'Paystack charge disabled successfully'];
    }
}
