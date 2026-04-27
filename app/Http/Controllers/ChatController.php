<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\RepairOrder;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'guest_session_id' => 'nullable|string',
            'context' => 'nullable|string',
            'repair_order_id' => 'nullable|integer|exists:repair_orders,id',
        ]);

        $user = auth()->user();
        $guestId = $request->guest_session_id;
        $repairOrderId = $request->integer('repair_order_id') ?: null;

        if ($repairOrderId) {
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để chat theo lệnh sửa chữa.'], 403);
            }

            $canAccessOrder = RepairOrder::where('id', $repairOrderId)
                ->where('customer_id', $user->id)
                ->exists();

            if (! $canAccessOrder) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền chat trong lệnh sửa chữa này.'], 403);
            }
        }

        $session = $this->findSession($user?->id, $guestId, $repairOrderId, true);

        if (! $session && $user && ! $repairOrderId && blank($request->context)) {
            $session = ChatSession::where('customer_id', $user->id)
                ->whereNotNull('repair_order_id')
                ->where('status', 'open')
                ->latest()
                ->first();
        }

        if (! $session) {
            $session = ChatSession::create([
                'customer_id' => $user ? $user->id : null,
                'guest_session_id' => $user ? null : $guestId,
                'repair_order_id' => $repairOrderId,
                'status' => 'open',
            ]);

            ChatMessage::create([
                'chat_session_id' => $session->id,
                'is_staff' => true,
                'message' => 'Chào bạn! Garage đã nhận được tin nhắn, nhân viên sẽ phản hồi sớm.',
                'is_read' => true,
            ]);
        }

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $attachmentPath = '/storage/' . $path;
        }

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_id' => $user ? $user->id : null,
            'is_staff' => false,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        $session->touch();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getMessages(Request $request)
    {
        $user = auth()->user();
        $guestId = $request->query('guest_session_id');
        $repairOrderId = $request->integer('repair_order_id') ?: null;

        if ($repairOrderId) {
            if (! $user) {
                return response()->json(['messages' => []], 403);
            }

            $canAccessOrder = RepairOrder::where('id', $repairOrderId)
                ->where('customer_id', $user->id)
                ->exists();

            if (! $canAccessOrder) {
                return response()->json(['messages' => []], 403);
            }
        }

        $session = $this->findSession($user?->id, $guestId, $repairOrderId, false);

        if (! $session) {
            return response()->json(['messages' => []]);
        }

        return response()->json(['messages' => $session->messages]);
    }

    private function findSession(?int $userId, ?string $guestId, ?int $repairOrderId, bool $openOnly): ?ChatSession
    {
        if ($userId) {
            return ChatSession::where('customer_id', $userId)
                ->when($openOnly, fn ($query) => $query->where('status', 'open'))
                ->when(
                    $repairOrderId,
                    fn ($query) => $query->where('repair_order_id', $repairOrderId),
                    fn ($query) => $query->whereNull('repair_order_id')
                )
                ->latest()
                ->first();
        }

        if ($guestId) {
            return ChatSession::where('guest_session_id', $guestId)
                ->whereNull('repair_order_id')
                ->when($openOnly, fn ($query) => $query->where('status', 'open'))
                ->latest()
                ->first();
        }

        return null;
    }
}
