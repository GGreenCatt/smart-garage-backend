<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        if (\App\Models\Setting::get('enable_notifications', '1') == '0') {
            return response()->json([]);
        }

        $notifications = Notification::where('notifiable_id', Auth::id())

            ->where('notifiable_type', get_class(Auth::user()))
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        return response()->json($notifications);
    }

    public function read($id)
    {
        $noti = Notification::where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->first();
            
        if ($noti) {
            $noti->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
