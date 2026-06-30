<?php

namespace App\Services;

use App\Models\BannerNotification;
use Illuminate\Support\Facades\DB;

class BannerNotificationService
{
    public function getActiveBanner(): ?array
    {
        $banner = BannerNotification::where('is_enabled', true)
            ->orderByDesc('updated_at')
            ->first();

        return $banner ? $banner->toArray() : null;
    }

    public function getAllBanners(): array
    {
        return BannerNotification::orderByDesc('updated_at')
            ->get()
            ->toArray();
    }

    public function getBannerById(int $id): ?array
    {
        $banner = BannerNotification::find($id);
        return $banner ? $banner->toArray() : null;
    }

    public function saveBanner(array $data): array
    {
        $id = $data['id'] ?? null;
        $message = $data['message'] ?? '';
        $isEnabled = $data['is_enabled'] ?? false;
        $speed = $data['speed'] ?? 50;
        $color = $data['color'] ?? 'blue';
        $backgroundColor = $data['background_color'] ?? 'blue';
        $textColor = $data['text_color'] ?? 'white';

        if (empty($message)) {
            return ['success' => false, 'message' => 'Message is required'];
        }

        if ($speed < 10 || $speed > 200) {
            return ['success' => false, 'message' => 'Speed must be between 10 and 200'];
        }

        if ($isEnabled) {
            BannerNotification::where('is_enabled', true)->update(['is_enabled' => false]);
        }

        if ($id) {
            $banner = BannerNotification::find($id);
            if (!$banner) {
                return ['success' => false, 'message' => 'Banner not found'];
            }

            $banner->update([
                'message' => $message,
                'is_enabled' => $isEnabled,
                'speed' => $speed,
                'color' => $color,
                'background_color' => $backgroundColor,
                'text_color' => $textColor,
            ]);
        } else {
            BannerNotification::create([
                'message' => $message,
                'is_enabled' => $isEnabled,
                'speed' => $speed,
                'color' => $color,
                'background_color' => $backgroundColor,
                'text_color' => $textColor,
            ]);
        }

        return ['success' => true, 'message' => 'Banner saved successfully'];
    }

    public function toggleBanner(int $id, bool $enabled): array
    {
        if ($enabled) {
            BannerNotification::where('is_enabled', true)->update(['is_enabled' => false]);
        }

        $banner = BannerNotification::find($id);
        if (!$banner) {
            return ['success' => false, 'message' => 'Banner not found'];
        }

        $banner->update(['is_enabled' => $enabled]);

        return ['success' => true, 'message' => 'Banner status updated'];
    }

    public function deleteBanner(int $id): array
    {
        $deleted = BannerNotification::where('id', $id)->delete();

        return $deleted
            ? ['success' => true, 'message' => 'Banner deleted successfully']
            : ['success' => false, 'message' => 'Failed to delete banner'];
    }
}
