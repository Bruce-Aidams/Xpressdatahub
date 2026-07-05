<?php

namespace App\Services;

use App\Models\CustomPricing;

class CustomPricingService
{
    public function setCustomPricing(array $data): array
    {
        $packageSize = $data['package_size'] ?? '';
        $cost = floatval($data['cost'] ?? 0);
        $sellingPrice = floatval($data['selling_price'] ?? 0);
        $userRole = $data['user_role'] ?? 'all';
        $networkType = $data['network_type'] ?? 'all';
        $createdBy = $data['created_by'] ?? null;

        $validRoles = ['agent', 'super_agent', 'dealers', 'administrator', 'all'];
        if (! in_array($userRole, $validRoles)) {
            return [
                'success' => false,
                'message' => 'Invalid user role. Only "agent", "super_agent", "dealers", "administrator", or "all" are allowed.',
            ];
        }

        $packageSizeGb = $this->parsePackageSizeToGb($packageSize);

        $existing = CustomPricing::where('package_size', $packageSize)
            ->where('network_type', $networkType)
            ->where('user_role', $userRole)
            ->first();

        if ($existing) {
            $existing->update([
                'cost' => $cost,
                'selling_price' => $sellingPrice,
                'package_size_gb' => $packageSizeGb,
                'updated_at' => now(),
            ]);

            return ['success' => true, 'action' => 'updated', 'id' => $existing->id];
        }

        $pricing = CustomPricing::create([
            'package_size' => $packageSize,
            'package_size_gb' => $packageSizeGb,
            'cost' => $cost,
            'selling_price' => $sellingPrice,
            'network_type' => $networkType,
            'user_role' => $userRole,
            'is_active' => true,
        ]);

        return ['success' => true, 'action' => 'created', 'id' => $pricing->id];
    }

    public function getCustomPricing(int $id): ?array
    {
        $pricing = CustomPricing::find($id);

        return $pricing ? $pricing->toArray() : null;
    }

    public function getAllCustomPricing(array $filters = []): array
    {
        $query = CustomPricing::query();

        if (! empty($filters['network_type'])) {
            $query->where('network_type', $filters['network_type']);
        }

        if (! empty($filters['user_role'])) {
            $query->where('user_role', $filters['user_role']);
        }

        return $query->orderBy('package_size_gb')
            ->get()
            ->toArray();
    }

    public function calculatePrice(string $packageSize, string $networkType = 'all', string $userRole = 'all'): array
    {
        $pricing = CustomPricing::where('package_size', $packageSize)
            ->where('network_type', $networkType)
            ->where('user_role', $userRole)
            ->where('is_active', true)
            ->first();

        if ($pricing) {
            return [
                'price' => $pricing->cost,
                'source' => 'custom',
                'pricing_id' => $pricing->id,
            ];
        }

        if ($userRole !== 'all') {
            $rolePricing = CustomPricing::where('package_size', $packageSize)
                ->where('network_type', $networkType)
                ->where('user_role', $userRole)
                ->where('is_active', true)
                ->first();

            if ($rolePricing) {
                return [
                    'price' => $rolePricing->cost,
                    'source' => 'role_custom',
                    'pricing_id' => $rolePricing->id,
                ];
            }
        }

        if ($networkType !== 'all') {
            $networkPricing = CustomPricing::where('package_size', $packageSize)
                ->where('network_type', $networkType)
                ->where('user_role', 'all')
                ->where('is_active', true)
                ->first();

            if ($networkPricing) {
                return [
                    'price' => $networkPricing->cost,
                    'source' => 'network_custom',
                    'pricing_id' => $networkPricing->id,
                ];
            }
        }

        return [
            'price' => null,
            'source' => 'not_found',
            'pricing_id' => null,
        ];
    }

    public function deleteCustomPricing(int $id): bool
    {
        return CustomPricing::where('id', $id)->delete() === 1;
    }

    public function parsePackageSizeToGb(string $packageSize): float
    {
        $packageSize = trim($packageSize);

        if (stripos($packageSize, 'MB') !== false) {
            $num = floatval(preg_replace('/[^0-9.]/', '', $packageSize));

            return round($num / 1024, 4);
        }

        if (stripos($packageSize, 'GB') !== false) {
            $num = floatval(preg_replace('/[^0-9.]/', '', $packageSize));

            return round($num, 4);
        }

        $num = floatval(preg_replace('/[^0-9.]/', '', $packageSize));

        return round($num, 4);
    }

    public function bulkImportPricing(array $data): array
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($data as $index => $item) {
            $result = $this->setCustomPricing([
                'package_size' => $item['package_size'] ?? '',
                'cost' => $item['cost'] ?? 0,
                'network_type' => $item['network_type'] ?? 'all',
                'user_role' => $item['user_role'] ?? 'all',
                'created_by' => $item['created_by'] ?? null,
            ]);

            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = 'Row '.($index + 1).': '.($result['message'] ?? 'Unknown error');
            }
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors,
        ];
    }
}
