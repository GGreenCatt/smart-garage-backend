<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialRequest;
use App\Models\RepairOrder;
use Illuminate\Support\Facades\Auth;

class MaterialRequestController extends Controller
{
    public function index()
    {
        // Load only the staff's own requests
        $requests = MaterialRequest::with('staff', 'repairOrder')
            ->where('staff_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('staff.requests.index', compact('requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:999',
            'repair_order_id' => 'nullable|exists:repair_orders,id',
            'reason' => 'nullable|string|max:1000'
        ]);

        if (! empty($validated['repair_order_id'])) {
            $order = RepairOrder::findOrFail($validated['repair_order_id']);

            if ($order->isLockedForStaffChanges() || in_array($order->status, ['pending_approval', 'approved'], true)) {
                return back()->withErrors(['part_name' => 'Không thể yêu cầu thêm vật tư cho phiếu đã khóa, đã gửi báo giá hoặc khách đã duyệt.']);
            }
        }

        MaterialRequest::create([
            'staff_id' => Auth::id(),
            'repair_order_id' => $validated['repair_order_id'] ?? null,
            'part_name' => $validated['part_name'],
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Đã gửi yêu cầu vật tư');
    }
}
