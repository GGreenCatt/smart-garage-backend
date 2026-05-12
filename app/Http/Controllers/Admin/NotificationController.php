<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', auth()->id())
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function read(string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', auth()->id())
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function readAll()
    {
        Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc');
    }
}
