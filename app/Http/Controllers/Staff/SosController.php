<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SosRequest;
use Illuminate\Support\Facades\Auth;

class SosController extends Controller
{
    /**
     * Display a listing of SOS requests.
     */
    public function index()
    {
        // Get pending SOS requests, checking user permissions or general availability
        // And also SOS requests assigned to the current staff member
        $pendingRequests = SosRequest::with(['customer', 'vehicle'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $myRequests = SosRequest::with(['customer', 'vehicle'])
            ->where('assigned_staff_id', Auth::id())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $completedCount = SosRequest::where('assigned_staff_id', Auth::id())
            ->where('status', 'completed')
            ->count();

        return view('staff.sos.index', compact('pendingRequests', 'myRequests', 'completedCount'));
    }

    /**
     * Display the specified SOS request.
     */
    public function show($id)
    {
        $sosInfo = SosRequest::with(['customer', 'vehicle'])->findOrFail($id);
        
        return view('staff.sos.show', compact('sosInfo'));
    }

    /**
     * Accept an SOS request.
     */
    public function accept(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);
        
        if ($sosRequest->status !== 'pending') {
            return response()->json([
                'success' => false, 
                'message' => 'Yêu cầu này đã có người xử lý hoặc đã kết thúc.'
            ], 400);
        }

        $sosRequest->status = 'assigned';
        $sosRequest->assigned_staff_id = Auth::id();
        $sosRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã nhận xử lý sự cố thành công!',
            'redirect' => route('staff.sos.show', $sosRequest->id)
        ]);
    }

    /**
     * Update the status of an SOS request.
     */
    public function updateStatus(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);
        
        // Ensure the staff is actually assigned to this SOS
        if ($sosRequest->assigned_staff_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn không có quyền cập nhật yêu cầu này.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled'
        ]);

        $sosRequest->status = $validated['status'];
        
        if ($validated['status'] === 'completed') {
            $sosRequest->completed_at = now();
        }

        $sosRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ]);
    }

    /**
     * Unassign the logged-in staff from an SOS request.
     */
    public function unassign(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);
        
        if ($sosRequest->assigned_staff_id !== Auth::id()) {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn không được phân công xử lý yêu cầu này.'
            ], 403);
        }

        if ($sosRequest->status === 'completed') {
            return response()->json([
                'success' => false, 
                'message' => 'Ca cứu hộ đã hoàn thành, không thể hủy.'
            ], 400);
        }

        $sosRequest->status = 'pending';
        $sosRequest->assigned_staff_id = null;
        $sosRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy nhận ca cứu hộ.',
            'redirect' => route('staff.sos.index')
        ]);
    }

    /**
     * Update the authenticated user's location.
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_sharing_location' => 'required|boolean'
        ]);

        $user = Auth::user();
        $user->latitude = $validated['latitude'];
        $user->longitude = $validated['longitude'];
        $user->is_sharing_location = $validated['is_sharing_location'];
        $user->last_location_update = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật vị trí thành công.'
        ]);
    }

    /**
     * Get locations of other staff members who are sharing their location.
     */
    public function getStaffLocations()
    {
        // Fetch staff who shared location in the last 5 minutes
        $staffMembers = \App\Models\User::where('is_sharing_location', true)
            ->where('id', '!=', Auth::id())
            ->where('last_location_update', '>=', now()->subMinutes(5))
            ->get(['id', 'name', 'latitude', 'longitude', 'last_location_update']);

        return response()->json([
            'success' => true,
            'data' => $staffMembers
        ]);
    }
}
