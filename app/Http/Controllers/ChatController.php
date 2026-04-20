<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'guest_session_id' => 'nullable|string',
            'context' => 'nullable|string' // e.g. "VHC #1"
        ]);

        $user = auth()->user();
        $guestId = $request->guest_session_id;

        // Find Session
        $session = null;
        if ($user) {
            $session = ChatSession::where('customer_id', $user->id)->where('status', 'open')->latest()->first();
        } elseif ($guestId) {
            $session = ChatSession::where('guest_session_id', $guestId)->where('status', 'open')->latest()->first();
        }

        // Create Session if not exists
        if (!$session) {
            $session = ChatSession::create([
                'customer_id' => $user ? $user->id : null,
                'guest_session_id' => $user ? null : $guestId,
                'status' => 'open'
            ]);
            
            // Auto-reply for new session
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'is_staff' => true,
                'message' => "Chào bạn! Chúng tôi có thể giúp gì cho bạn hôm nay?",
                'is_read' => true
            ]);
        }

        // Handle Image Upload
        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $attachmentPath = '/storage/' . $path;
        }

        // Save User Message
        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_id' => $user ? $user->id : null,
            'is_staff' => false,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'is_read' => false
        ]);

        // Simulated Auto-Reply logic (Simple Bot)
        // In real app, this would be handled by Staff or Async Job
        // For MVP, if message contains "lỗi", reply generic
        if ($request->message && (str_contains(strtolower($request->message), 'lỗi') || str_contains(strtolower($request->message), 'giá'))) {
            sleep(1); // Simulate think
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'is_staff' => true,
                'message' => "Cảm ơn bạn. Kỹ thuật viên đã nhận được thông tin và sẽ phản hồi trong ít phút.",
                'is_read' => false // Unread for user? actually user sees it immediately
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function getMessages(Request $request)
    {
        $user = auth()->user();
        $guestId = $request->query('guest_session_id');

        $session = null;
        if ($user) {
            $session = ChatSession::where('customer_id', $user->id)->latest()->first();
        } elseif ($guestId) {
            $session = ChatSession::where('guest_session_id', $guestId)->latest()->first();
        }

        if (!$session) {
            return response()->json(['messages' => []]);
        }

        return response()->json(['messages' => $session->messages]);
    }
}
