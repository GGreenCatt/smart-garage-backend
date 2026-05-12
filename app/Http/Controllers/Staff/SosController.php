<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SosRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SosController extends Controller
{
    public function index()
    {
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

        $historyRequests = SosRequest::with(['customer', 'vehicle', 'cancelledBy'])
            ->whereIn('status', ['completed', 'cancelled'])
            ->where(function ($query) {
                $query->where('assigned_staff_id', Auth::id())
                    ->orWhere('cancelled_by', Auth::id());
            })
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return view('staff.sos.index', compact('pendingRequests', 'myRequests', 'completedCount', 'historyRequests'));
    }

    public function show($id)
    {
        $sosInfo = SosRequest::with(['customer', 'vehicle', 'assignedStaff', 'cancelledBy'])->findOrFail($id);

        return view('staff.sos.show', compact('sosInfo'));
    }

    public function pendingAlert()
    {
        $pendingRequests = SosRequest::with(['customer', 'vehicle'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $pendingRequests->count(),
            'latest' => $pendingRequests->last()?->only(['id', 'description', 'created_at']),
            'items' => $pendingRequests->map(fn ($sos) => [
                'id' => $sos->id,
                'display_name' => $sos->display_name,
                'display_phone' => $sos->display_phone,
                'vehicle' => $sos->vehicle?->license_plate ?? 'Xe ngoài hệ thống',
                'description' => Str::limit((string) $sos->description, 120),
                'created_at' => $sos->created_at?->toIso8601String(),
                'url' => route('staff.sos.show', $sos->id),
            ])->values(),
        ]);
    }

    public function accept(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);

        if ($sosRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu này đã có người xử lý hoặc đã kết thúc.',
            ], 400);
        }

        $sosRequest->status = 'assigned';
        $sosRequest->assigned_staff_id = Auth::id();
        $sosRequest->save();

        $this->logSosActivity($sosRequest, 'STAFF_SOS_ACCEPTED', 'Nhận xử lý ca cứu hộ.');

        return response()->json([
            'success' => true,
            'message' => 'Đã nhận xử lý sự cố thành công!',
            'redirect' => route('staff.sos.show', $sosRequest->id),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);

        if ($sosRequest->assigned_staff_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật yêu cầu này.',
            ], 403);
        }

        if (in_array($sosRequest->status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Ca cứu hộ đã kết thúc, không thể cập nhật trạng thái.',
            ], 409);
        }

        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
        ]);

        $oldStatus = $sosRequest->status;
        $sosRequest->status = $validated['status'];

        if ($validated['status'] === 'completed') {
            $sosRequest->completed_at = now();
        }

        $sosRequest->save();

        $this->logSosActivity(
            $sosRequest,
            'STAFF_SOS_STATUS_UPDATED',
            "Cập nhật trạng thái SOS từ {$oldStatus} sang {$sosRequest->status}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!',
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);

        if (in_array($sosRequest->status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Ca cứu hộ đã kết thúc, không thể hủy.',
            ], 409);
        }

        if ($sosRequest->assigned_staff_id && $sosRequest->assigned_staff_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không được phân công xử lý yêu cầu này.',
            ], 403);
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|in:customer_cancelled,duplicate_request,invalid_location,unable_to_contact,outside_service_area,other',
            'cancel_note' => 'required|string|min:5|max:1000',
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy.',
            'cancel_note.required' => 'Vui lòng nhập nội dung hủy.',
            'cancel_note.min' => 'Nội dung hủy cần tối thiểu 5 ký tự.',
        ]);

        $reasonLabels = $this->cancelReasonLabels();
        $reasonLabel = $reasonLabels[$validated['cancel_reason']] ?? 'Khác';

        $sosRequest->status = 'cancelled';
        $sosRequest->cancel_reason = $validated['cancel_reason'];
        $sosRequest->cancel_note = $validated['cancel_note'];
        $sosRequest->cancelled_at = now();
        $sosRequest->cancelled_by = Auth::id();
        $sosRequest->save();

        $this->logSosActivity(
            $sosRequest,
            'STAFF_SOS_CANCELLED',
            "Hủy SOS #{$sosRequest->id}. Lý do: {$reasonLabel}. Nội dung: {$validated['cancel_note']}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy yêu cầu cứu hộ.',
            'redirect' => route('staff.sos.show', $sosRequest->id),
        ]);
    }

    public function unassign(Request $request, $id)
    {
        $sosRequest = SosRequest::findOrFail($id);

        if ($sosRequest->assigned_staff_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không được phân công xử lý yêu cầu này.',
            ], 403);
        }

        if ($sosRequest->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Ca cứu hộ đã hoàn thành, không thể hủy.',
            ], 400);
        }

        if ($sosRequest->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Ca cứu hộ đã hủy, không thể nhận/trả ca.',
            ], 409);
        }

        $sosRequest->status = 'pending';
        $sosRequest->assigned_staff_id = null;
        $sosRequest->save();

        $this->logSosActivity($sosRequest, 'STAFF_SOS_UNASSIGNED', 'Trả ca cứu hộ về danh sách chờ.');

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy nhận ca cứu hộ.',
            'redirect' => route('staff.sos.index'),
        ]);
    }

    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required_if:is_sharing_location,true|nullable|numeric',
            'longitude' => 'required_if:is_sharing_location,true|nullable|numeric',
            'is_sharing_location' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->latitude = $validated['latitude'] ?? null;
        $user->longitude = $validated['longitude'] ?? null;
        $user->is_sharing_location = $validated['is_sharing_location'];
        $user->last_location_update = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật vị trí thành công.',
        ]);
    }

    public function getStaffLocations()
    {
        $staffMembers = User::where('is_sharing_location', true)
            ->where('role', 'staff')
            ->where('id', '!=', Auth::id())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('last_location_update', '>=', now()->subMinutes(5))
            ->get(['id', 'name', 'latitude', 'longitude', 'last_location_update']);

        return response()->json([
            'success' => true,
            'data' => $staffMembers,
        ]);
    }

    private function cancelReasonLabels(): array
    {
        return [
            'customer_cancelled' => 'Khách hủy yêu cầu',
            'duplicate_request' => 'Yêu cầu bị trùng',
            'invalid_location' => 'Vị trí không hợp lệ',
            'unable_to_contact' => 'Không liên hệ được khách',
            'outside_service_area' => 'Ngoài khu vực hỗ trợ',
            'other' => 'Khác',
        ];
    }

    private function logSosActivity(SosRequest $sosRequest, string $action, string $details): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details . " Khách: {$sosRequest->display_name} - {$sosRequest->display_phone}.",
            'ip_address' => request()->ip(),
        ]);
    }
}
