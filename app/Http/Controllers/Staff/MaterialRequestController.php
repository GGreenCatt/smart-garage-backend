<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialRequest;
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
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string'
        ]);

        MaterialRequest::create([
            'staff_id' => Auth::id(),
            'part_name' => $validated['part_name'],
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason']
        ]);

        return back()->with('success', 'Đã gửi yêu cầu vật tư');
    }

    public function updateStatus(Request $request, $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        $materialRequest->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note']
        ]);

        if ($validated['status'] === 'approved' && $materialRequest->repair_order_id) {
             // Create an external part item automatically
             \App\Models\RepairOrderItem::create([
                 'repair_order_id' => $materialRequest->repair_order_id,
                 'name' => 'Vật tư ngoài: ' . $materialRequest->part_name,
                 'quantity' => $materialRequest->quantity,
                 'cost_price' => $materialRequest->cost_price ?? 0,
                 'unit_price' => $materialRequest->unit_price ?? 0,
                 'subtotal' => $materialRequest->quantity * ($materialRequest->unit_price ?? 0),
             ]);

             // Recalculate complete order total
             $order = \App\Models\RepairOrder::find($materialRequest->repair_order_id);
             if ($order) {
                 $order->total_amount = $order->items()->sum('subtotal');
                 $order->save();
             }
        }

        return response()->json(['success' => true]);
    }
}
