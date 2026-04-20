<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InternalMessage;

class InternalChatController extends Controller
{
    public function getMessages(Request $request)
    {
        $currentUserId = auth()->id();

        // Mark group messages as "read" for this user (could be more complex, but for now we just fetch)
        // InternalMessage::where('is_group', true)->where('sender_id', '!=', $currentUserId)...

        // Get group messages
        $messages = InternalMessage::where('is_group', true)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Todo: handle image uploads if we want image support internally later.

        $message = InternalMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => null, // Broadcast to all
            'is_group' => true,
            'message' => $request->message ?? '',
        ]);

        // Load sender relation for the response
        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
