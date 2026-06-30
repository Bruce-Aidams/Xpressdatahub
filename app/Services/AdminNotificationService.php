<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Agent;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    public function sendNotification(array $data): array
    {
        $title = $data['title'] ?? '';
        $message = $data['message'] ?? '';
        $senderId = $data['sender_id'] ?? 0;
        $recipientType = $data['recipient_type'] ?? 'all';
        $recipientIds = $data['recipient_ids'] ?? null;
        $priority = $data['priority'] ?? 'normal';
        $expiresAt = $data['expires_at'] ?? null;

        if (empty($title) || empty($message) || empty($senderId)) {
            return ['success' => false, 'message' => 'Title, message, and sender ID are required'];
        }

        $validTypes = ['all', 'agents', 'super_agents', 'specific', 'admin'];
        if (!in_array($recipientType, $validTypes)) {
            return ['success' => false, 'message' => 'Invalid recipient type'];
        }

        $validPriorities = ['low', 'normal', 'high', 'urgent'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'normal';
        }

        $recipientIdsJson = null;
        if ($recipientType === 'specific' && is_array($recipientIds)) {
            $recipientIdsJson = json_encode($recipientIds);
        }

        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'sender_id' => $senderId,
            'sender_type' => 'admin',
            'recipient_type' => $recipientType,
            'recipient_ids' => $recipientIdsJson,
            'priority' => $priority,
            'expires_at' => $expiresAt,
        ]);

        return [
            'success' => true,
            'message' => 'Notification sent successfully',
            'notification_id' => $notification->id,
        ];
    }

    public function getUserNotifications(int $userId, string $userType, int $limit = 50, int $offset = 0): array
    {
        $query = Notification::query()
            ->leftJoin('notification_reads', function ($join) use ($userId) {
                $join->on('notifications.id', '=', 'notification_reads.notification_id')
                    ->where('notification_reads.user_id', '=', $userId);
            })
            ->where(function ($q) use ($userType, $userId) {
                $q->where('notifications.recipient_type', 'all')
                    ->orWhere('notifications.recipient_type', $userType)
                    ->orWhere(function ($q2) use ($userType) {
                        if ($userType === 'agent') {
                            $q2->where('notifications.recipient_type', 'agents');
                        } elseif ($userType === 'super_agent') {
                            $q2->where('notifications.recipient_type', 'super_agents');
                        }
                    })
                    ->orWhere(function ($q2) use ($userId) {
                        $q2->where('notifications.recipient_type', 'specific')
                            ->where('notifications.recipient_ids', 'LIKE', '%"' . $userId . '"%');
                    });
            })
            ->where(function ($q) {
                $q->whereNull('notifications.expires_at')
                    ->orWhere('notifications.expires_at', '>', now());
            })
            ->select('notifications.*', 'notification_reads.read_at')
            ->orderByDesc('notifications.priority')
            ->orderByDesc('notifications.created_at')
            ->offset($offset)
            ->limit($limit);

        return $query->get()->toArray();
    }

    public function markAsRead(int $notificationId, int $userId): array
    {
        $notification = Notification::find($notificationId);
        if (!$notification) {
            return ['success' => false, 'message' => 'Notification not found'];
        }

        $existing = NotificationRead::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return ['success' => true, 'message' => 'Already marked as read'];
        }

        NotificationRead::create([
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'read_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Marked as read'];
    }

    public function markAllAsRead(int $userId): array
    {
        $unreadNotifications = $this->getUserNotifications($userId, 'agent', 1000, 0);
        $markedCount = 0;

        foreach ($unreadNotifications as $notification) {
            $result = $this->markAsRead($notification['id'], $userId);
            if ($result['success']) {
                $markedCount++;
            }
        }

        return ['success' => true, 'message' => "Marked $markedCount notifications as read"];
    }

    public function getUnreadCount(int $userId, ?string $type = null): int
    {
        $query = Notification::query()
            ->leftJoin('notification_reads', function ($join) use ($userId) {
                $join->on('notifications.id', '=', 'notification_reads.notification_id')
                    ->where('notification_reads.user_id', '=', $userId);
            })
            ->whereNull('notification_reads.id')
            ->where(function ($q) {
                $q->whereNull('notifications.expires_at')
                    ->orWhere('notifications.expires_at', '>', now());
            });

        if ($type) {
            $query->where('notifications.type', $type);
        }

        return $query->count();
    }

    public function getAllNotifications(array $filters = []): array
    {
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        return Notification::orderByDesc('created_at')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function deleteNotification(int $id): array
    {
        $deleted = Notification::where('id', $id)->delete();
        return $deleted
            ? ['success' => true, 'message' => 'Notification deleted successfully']
            : ['success' => false, 'message' => 'Failed to delete notification'];
    }

    public function notifyAdmins(array $data): array
    {
        $title = $data['title'] ?? '';
        $message = $data['message'] ?? '';
        $priority = $data['priority'] ?? 'urgent';

        report("ADMIN NOTIFICATION [$priority]: $title - $message");

        try {
            $adminIds = AdminUser::where('is_active', true)->pluck('id')->toArray();

            if (empty($adminIds)) {
                return ['success' => false, 'message' => 'No active admins found'];
            }

            $notification = Notification::create([
                'title' => $title,
                'message' => "[SYSTEM ALERT] $message",
                'sender_id' => 0,
                'sender_type' => 'system',
                'recipient_type' => 'admin',
                'priority' => $priority,
            ]);

            return [
                'success' => true,
                'message' => 'Admin notification sent successfully',
                'notification_id' => $notification->id,
                'admin_count' => count($adminIds),
            ];
        } catch (\Exception $e) {
            report($e);
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}
