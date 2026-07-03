<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerNotification;
use App\Services\BannerNotificationService;
use Illuminate\Http\Request;

class AdminBannerController extends Controller
{
    public function __construct(
        private BannerNotificationService $bannerService
    ) {}

    public function index()
    {
        $banners = BannerNotification::orderByDesc('created_at')->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'speed' => 'nullable|integer|min:10|max:200',
            'color' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
        ]);

        try {
            $result = $this->bannerService->saveBanner([
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'is_enabled' => $request->boolean('is_enabled', false),
                'speed' => $request->input('speed', 50),
                'color' => $request->input('color', 'blue'),
                'background_color' => $request->input('background_color', 'blue'),
                'text_color' => $request->input('text_color', 'white'),
            ]);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Banner saved successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to save banner.');
        }
    }

    public function update(Request $request, BannerNotification $bannerNotification)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'speed' => 'nullable|integer|min:10|max:200',
            'color' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
        ]);

        try {
            $bannerData = is_array($bannerNotification->data) ? $bannerNotification->data : [];

            $result = $this->bannerService->saveBanner([
                'id' => $bannerNotification->id,
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'is_enabled' => $request->boolean('is_enabled', $bannerNotification->is_active),
                'speed' => $request->input('speed', $bannerData['speed'] ?? 50),
                'color' => $request->input('color', $bannerData['color'] ?? 'blue'),
                'background_color' => $request->input('background_color', $bannerData['background_color'] ?? '#1e40af'),
                'text_color' => $request->input('text_color', $bannerData['text_color'] ?? '#ffffff'),
            ]);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Banner updated successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update banner.');
        }
    }

    public function toggle(BannerNotification $bannerNotification)
    {
        try {
            $bannerNotification->update(['is_active' => ! $bannerNotification->is_active]);

            return redirect()->back()
                ->with('success', 'Banner status toggled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle banner status.');
        }
    }

    public function destroy(BannerNotification $bannerNotification)
    {
        try {
            $result = $this->bannerService->deleteBanner($bannerNotification->id);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Banner deleted successfully.');
            }

            return redirect()->back()
                ->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete banner.');
        }
    }
}
