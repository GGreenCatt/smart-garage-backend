<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class StaffChatController extends Controller
{
    public function index()
    {
        // View is handled by frontend polling, but we pass initial sessions
        return view('staff.chat.index');
    }

    public function getSessions()
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        $query = ChatSession::with(['messages', 'customer', 'repairOrder.vehicle'])
            ->where('status', 'open');

        if (!$isAdmin) {
            // Only show sessions where this mechanic has tasks in the repair order
            $query->whereHas('repairOrder.tasks', function($q) use ($userId) {
                $q->where('mechanic_id', $userId);
            });
        }

        $sessions = $query->orderBy('updated_at', 'desc')->get();
            
        return response()->json(['sessions' => $sessions]);
    }

    public function searchCustomer(Request $request)
    {
        // Disabled as per new requirement: chats are auto-created from jobs
        return response()->json(['customers' => []]);
    }

    public function startSession(Request $request)
    {
        // Disabled: chats are auto-created from StaffController status hook
        return response()->json(['success' => false, 'message' => 'Vui lòng bắt đầu công việc để tạo chat.']);
    }

    public function reply(Request $request)
    {
        $request->validate([
            'chat_session_id' => 'required|exists:chat_sessions,id',
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $session = ChatSession::findOrFail($request->chat_session_id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        // Permission check: only assigned mechanic or admin
        if (!$isAdmin) {
            $isAssigned = \App\Models\RepairTask::where('repair_order_id', $session->repair_order_id)
                ->where('mechanic_id', $userId)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền chat trong công việc này.'], 403);
            }
        }

        if ($session->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Cuộc hội thoại này đã kết thúc.'], 403);
        }

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $attachmentPath = '/storage/' . $path;
        }

        $message = ChatMessage::create([
            'chat_session_id' => $request->chat_session_id,
            'is_staff' => true,
            'sender_id' => $userId,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'is_read' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
