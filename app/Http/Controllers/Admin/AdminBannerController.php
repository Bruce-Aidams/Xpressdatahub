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
            'message' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'speed' => 'nullable|integer|min:10|max:200',
            'color' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
        ]);

        try {
            $result = $this->bannerService->saveBanner([
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
            'message' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'speed' => 'nullable|integer|min:10|max:200',
            'color' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
        ]);

        try {
            $result = $this->bannerService->saveBanner([
                'id' => $bannerNotification->id,
                'message' => $request->input('message'),
                'is_enabled' => $request->boolean('is_enabled', $bannerNotification->is_enabled),
                'speed' => $request->input('speed', $bannerNotification->speed ?? 50),
                'color' => $request->input('color', $bannerNotification->color ?? 'blue'),
                'background_color' => $request->input('background_color', $bannerNotification->background_color ?? 'blue'),
                'text_color' => $request->input('text_color', $bannerNotification->text_color ?? 'white'),
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
