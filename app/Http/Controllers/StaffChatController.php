<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\RepairTask;
use Illuminate\Http\Request;

class StaffChatController extends Controller
{
    public function index()
    {
        return view('staff.chat.index');
    }

    public function getSessions()
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        $query = ChatSession::with(['messages.sender', 'customer', 'repairOrder.vehicle'])
            ->where('status', 'open');

        if (! $isAdmin) {
            $query->where(function ($chatQuery) use ($userId) {
                $chatQuery->whereNull('repair_order_id')
                    ->orWhereHas('repairOrder', function ($orderQuery) use ($userId) {
                        $orderQuery->where('advisor_id', $userId)
                            ->orWhereHas('tasks', function ($taskQuery) use ($userId) {
                                $taskQuery->where('mechanic_id', $userId);
                            });
                    });
            });
        }

        $sessions = $query->orderBy('updated_at', 'desc')->get();

        return response()->json(['sessions' => $sessions]);
    }

    public function searchCustomer(Request $request)
    {
        return response()->json(['customers' => []]);
    }

    public function startSession(Request $request)
    {
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

        if (! $isAdmin && ! $this->canAccessSession($session, $userId)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chat trong công việc này.'], 403);
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
            'is_read' => true,
        ]);

        $session->touch();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    private function canAccessSession(ChatSession $session, int $userId): bool
    {
        if (! $session->repair_order_id) {
            return true;
        }

        $session->loadMissing('repairOrder');

        if ((int) ($session->repairOrder?->advisor_id) === $userId) {
            return true;
        }

        return RepairTask::where('repair_order_id', $session->repair_order_id)
            ->where('mechanic_id', $userId)
            ->exists();
    }
}
