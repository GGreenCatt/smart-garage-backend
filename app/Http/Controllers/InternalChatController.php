<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InternalMessage;

class InternalChatController extends Controller
{
    public function getContacts()
    {
        $currentUserId = auth()->id();
        
        // Get all staff and admins except current user
        $contacts = User::whereIn('role', ['admin', 'staff'])
            ->where('id', '!=', $currentUserId)
            ->get()
            ->map(function ($user) use ($currentUserId) {
                // Get the latest message between current user and this contact
                $lastMsg = InternalMessage::where(function ($q) use ($currentUserId, $user) {
                        $q->where('sender_id', $currentUserId)->where('receiver_id', $user->id);
                    })
                    ->orWhere(function ($q) use ($currentUserId, $user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $currentUserId);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Has unread messages matching $user -> $currentUserId
                $hasUnread = InternalMessage::where('sender_id', $user->id)
                    ->where('receiver_id', $currentUserId)
                    ->whereNull('read_at')
                    ->exists();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'last_message' => $lastMsg ? clone $lastMsg : null,
                    'unreads' => $hasUnread
                ];
            });

        // Sort by last message time (descending), then alphabetically
        $contacts = $contacts->sortByDesc(function ($c) {
            return $c['last_message'] ? $c['last_message']->created_at : now()->subYears(10);
        })->values();

        return response()->json(['contacts' => $contacts]);
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUserId = auth()->id();
        $otherUserId = $request->user_id;

        // Mark incoming messages as read
        InternalMessage::where('sender_id', $otherUserId)
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get messages
        $messages = InternalMessage::where(function ($q) use ($currentUserId, $otherUserId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($q) use ($currentUserId, $otherUserId) {
                $q->where('sender_id', $otherUserId)->where('receiver_id', $currentUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Todo: handle image uploads if we want image support internally later.

        $message = InternalMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
