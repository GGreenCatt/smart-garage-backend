<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class StaffChatController extends Controller
{
    public function index()
    {
        // Get open sessions first, then closed
        $sessions = ChatSession::with(['messages', 'customer'])
            ->orderByRaw("FIELD(status, 'open', 'closed')")
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('staff.chat.index', compact('sessions'));
    }

    public function getSessions()
    {
        $sessions = ChatSession::with(['messages', 'customer'])
            ->orderByRaw("FIELD(status, 'open', 'closed')")
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return response()->json(['sessions' => $sessions]);
    }

    public function searchCustomer(Request $request)
    {
        $query = $request->get('query');
        if (empty($query)) return response()->json(['customers' => []]);

        $customers = \App\Models\User::where('role', 'customer')
            ->where(function($q) use ($query) {
                $q->where('phone', 'like', "%$query%")
                  ->orWhere('name', 'like', "%$query%");
            })
            ->limit(10)
            ->get();

        return response()->json(['customers' => $customers]);
    }

    public function startSession(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id'
        ]);

        // Check for existing open session for this customer
        $session = ChatSession::where('customer_id', $request->customer_id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'customer_id' => $request->customer_id,
                'status' => 'open'
            ]);
        }

        return response()->json([
            'success' => true,
            'session_id' => $session->id
        ]);
    }

    public function reply(Request $request)
    {
        $request->validate([
            'chat_session_id' => 'required|exists:chat_sessions,id',
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $attachmentPath = '/storage/' . $path;
        }

        $message = ChatMessage::create([
            'chat_session_id' => $request->chat_session_id,
            'is_staff' => true,
            'sender_id' => auth()->id(),
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'is_read' => true // Staff read their own message
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
