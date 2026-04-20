<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Show the quote details including VHC report, tasks, items and 3D data.
     */
    public function show(Request $request, \App\Models\RepairOrder $repairOrder)
    {
        // Require auth customer ownership OR valid guest signed route
        $isGuestRoute = str_starts_with($request->route()->getName(), 'guest.');
        
        if (!$isGuestRoute && (!auth()->check() || $repairOrder->customer_id !== $request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load necessary relations
        $repairOrder->load([
            'vehicle',
            'advisor:id,name,phone',
            'vhcReport.defects',
            'tasks.children', // Load task hierarchies if they exist
            'items'
        ]);

        return view('customer.quote', [
            'order' => $repairOrder
        ]);
    }

    /**
     * Approve or reject specific tasks.
     * Expects payload: { tasks: [{ id: 1, status: 'approved' }, { id: 2, status: 'rejected' }] }
     */
    public function approveRejectTasks(Request $request, \App\Models\RepairOrder $repairOrder)
    {
        $isGuestRoute = str_starts_with($request->route()->getName(), 'guest.');

        if (!$isGuestRoute && (!auth()->check() || $repairOrder->customer_id !== $request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:repair_tasks,id',
            'tasks.*.status' => 'required|in:approved,rejected',
            'customer_note' => 'nullable|string'
        ]);

        $tasks = $validated['tasks'];
        $allApproved = true;

        foreach ($tasks as $taskData) {
            $task = \App\Models\RepairTask::find($taskData['id']);
            
            // Security check: ensure task belongs to this sequence
            if ($task->repair_order_id !== $repairOrder->id) {
                continue;
            }

            $task->update([
                'customer_approval_status' => $taskData['status']
            ]);

            if ($taskData['status'] === 'rejected') {
                $allApproved = false;
            }
        }

        // Save customer note if provided
        if (isset($validated['customer_note'])) {
            $repairOrder->update([
                'customer_note' => $validated['customer_note']
            ]);
        }

        // Check if there are still any pending tasks (exclude parent tasks)
        $hasPendingTasks = $repairOrder->tasks()
            ->whereNotNull('parent_id')
            ->where(function ($q) {
                $q->whereNull('customer_approval_status')
                  ->orWhere('customer_approval_status', 'pending');
            })
            ->exists();

        // Update overall repair order status based on task approvals
        if (!$hasPendingTasks) {
            if ($allApproved) {
                $repairOrder->update(['status' => 'approved']);
            } else {
                // Determine what status means "partially approved" or "declined tasks". 
                // We'll use 'approved' as well since work can commence on the approved pieces, 
                // or just leave it pending if we need staff review. 
                // Using 'approved' assuming any approved work means go ahead.
                $hasApprovedTasks = $repairOrder->tasks()->where('customer_approval_status', 'approved')->exists();
                if ($hasApprovedTasks) {
                    $repairOrder->update(['status' => 'approved']);
                } else {
                    $repairOrder->update(['status' => 'cancelled']); // or 'rejected' depending on existing enum
                }
            }
            
            // Notify staff
            $advisor = \App\Models\User::find($repairOrder->advisor_id);
            if ($advisor) {
                \App\Services\NotificationService::send(
                    $advisor,
                    'quote_reviewed',
                    'Phản hồi Báo Giá',
                    "Khách hàng đã phản hồi báo giá cho đơn sửa chữa #{$repairOrder->id}.",
                    route('staff.order.show', $repairOrder->id),
                    'fas fa-file-invoice-dollar'
                );
            }
        }

        return response()->json([
            'message' => 'Task approvals updated.',
            'repair_order' => $repairOrder->fresh()
        ]);
    }
}
