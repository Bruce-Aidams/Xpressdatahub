<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notificationService
    ) {}

    public function index()
    {
        $notifications = $this->notificationService->getAllNotifications([
            'limit' => 50,
        ]);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|string|in:all,agents,super_agents,specific,admin',
            'recipient_ids' => 'nullable|array',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        try {
            $result = $this->notificationService->sendNotification([
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'sender_id' => session('admin_id'),
                'sender_type' => 'admin',
                'recipient_type' => $request->input('recipient_type'),
                'recipient_ids' => $request->input('recipient_ids'),
                'priority' => $request->input('priority', 'normal'),
                'type' => 'admin',
            ]);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Notification sent successfully.');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $result['message'] ?? 'Failed to send notification.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while sending notification.');
        }
    }
}
