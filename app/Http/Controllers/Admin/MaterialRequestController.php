<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialRequest;
use Illuminate\Support\Facades\Gate;

class MaterialRequestController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_inventory'); // Or manage_vehicles if inventory gate missing
        
        $pendingRequests = MaterialRequest::with('staff')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $historyRequests = MaterialRequest::with('staff')
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();
            
        return view('admin.requests.index', compact('pendingRequests', 'historyRequests'));
    }

    public function update(Request $request, MaterialRequest $materialRequest)
    {
        Gate::authorize('manage_inventory');

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        // Transaction to ensure data integrity
        \DB::transaction(function () use ($materialRequest, $validated) {
            $materialRequest->update([
                'status' => $validated['status'],
                'admin_note' => $validated['admin_note'] ?? null
            ]);

            // If Approved and has Repair Order, create the item
            if ($validated['status'] === 'approved' && $materialRequest->repair_order_id) {
                $order = \App\Models\RepairOrder::find($materialRequest->repair_order_id);
                if ($order) {
                    $order->items()->create([
                        'name' => $materialRequest->part_name,
                        'quantity' => $materialRequest->quantity,
                        'cost_price' => $materialRequest->cost_price ?? 0,
                        'unit_price' => $materialRequest->unit_price ?? 0,
                        'subtotal' => $materialRequest->quantity * ($materialRequest->unit_price ?? 0),
                        'itemable_type' => null,
                        'itemable_id' => null,
                    ]);
                    
                    // Recalculate Order Total
                    $order->total_amount = $order->items()->sum('subtotal');
                    $order->save();
                }
            }
        });

        return back()->with('success', 'Đã cập nhật trạng thái yêu cầu');
    }
}
