<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\AdminNotificationService;

class UserNotificationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notificationService
    ) {}

    public function index()
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);
        $userType = $agent->role ?? 'agent';

        $notifications = $this->notificationService->getUserNotifications($userId, $userType, 50);
        $unreadCount = $this->notificationService->getUnreadCount($userId, $userType);

        return view('user.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(int $id)
    {
        $userId = session('user_id');
        $this->notificationService->markAsRead($id, $userId);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $userId = session('user_id');
        $agent = Agent::find($userId);
        $userType = $agent->role ?? 'agent';
        $this->notificationService->markAllAsRead($userId, $userType);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
