<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MaterialRequest;
use App\Models\RepairOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MaterialRequestController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_inventory');

        $pendingRequests = MaterialRequest::with(['staff', 'repairOrder.vehicle'])
            ->where('status', 'pending')
            ->oldest()
            ->get();

        $historyRequests = MaterialRequest::with(['staff', 'repairOrder.vehicle'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')
            ->take(30)
            ->get();

        $todayCount = MaterialRequest::where(function ($query) {
            $query->whereDate('created_at', today())
                ->orWhereDate('updated_at', today());
        })->count();

        return view('admin.requests.index', compact('pendingRequests', 'historyRequests', 'todayCount'));
    }

    public function update(Request $request, MaterialRequest $materialRequest)
    {
        Gate::authorize('manage_inventory');

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó');
        }

        if ($validated['status'] === 'rejected' && blank($validated['admin_note'] ?? null)) {
            return back()->with('error', 'Vui lòng nhập lý do từ chối');
        }

        if ($validated['status'] === 'approved' && $materialRequest->repair_order_id) {
            $order = RepairOrder::find($materialRequest->repair_order_id);
            if ($order && $order->isLockedForStaffChanges()) {
                return back()->with('error', 'Phiếu sửa chữa đã khóa nên không thể thêm vật tư');
            }
        }

        DB::transaction(function () use ($materialRequest, $validated, $request) {
            $materialRequest->update([
                'status' => $validated['status'],
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            if ($validated['status'] === 'approved' && $materialRequest->repair_order_id) {
                $order = RepairOrder::with('promotion')->find($materialRequest->repair_order_id);

                if ($order && ! $order->isLockedForStaffChanges()) {
                    $order->items()->create([
                        'name' => 'Vật tư ngoài: '.$materialRequest->part_name,
                        'quantity' => $materialRequest->quantity,
                        'cost_price' => $materialRequest->cost_price ?? 0,
                        'unit_price' => $materialRequest->unit_price ?? 0,
                        'subtotal' => $materialRequest->quantity * ($materialRequest->unit_price ?? 0),
                        'itemable_type' => null,
                        'itemable_id' => null,
                    ]);

                    $this->recalculateOrderTotal($order);
                }
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'PROCESS_MATERIAL_REQUEST',
                'details' => ($validated['status'] === 'approved' ? 'Duyệt' : 'Từ chối')." yêu cầu vật tư {$materialRequest->part_name} (#{$materialRequest->id})",
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'Đã cập nhật yêu cầu vật tư');
    }

    private function recalculateOrderTotal(RepairOrder $order): void
    {
        $subtotal = $order->items()->sum('subtotal');
        $discount = 0;

        if ($order->promotion_id && $order->promotion?->isValid()) {
            $discount = $order->promotion->type === 'fixed'
                ? $order->promotion->value
                : $subtotal * ($order->promotion->value / 100);
        }

        $discount = min($discount, $subtotal);

        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => 0,
            'total_amount' => $subtotal - $discount,
        ]);
    }
}
