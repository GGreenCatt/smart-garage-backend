<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification to a specific User
     * 
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param string|null $link
     * @param string $icon
     */
    public static function send(User $user, $type, $title, $message, $link = null, $icon = 'fas fa-bell')
    {
        return Notification::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => $type,
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => [
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'icon' => $icon
            ]
        ]);
    }

    /**
     * Send notification to all active Staff members
     */
    public static function notifyAllStaff($type, $title, $message, $link = null, $icon = 'fas fa-bell')
    {
        // Assuming staff members have role 'staff' or 'admin'
        $staffMembers = User::whereIn('role', ['staff', 'admin'])->get();
        
        foreach ($staffMembers as $staff) {
            self::send($staff, $type, $title, $message, $link, $icon);
        }
    }
}
