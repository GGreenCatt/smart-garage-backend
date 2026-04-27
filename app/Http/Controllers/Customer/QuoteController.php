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
        
        if (!$isGuestRoute && (! auth()->check() || ! $this->customerOwnsOrder($repairOrder, $request->user()))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load necessary relations
        $repairOrder->load([
            'vehicle',
            'advisor:id,name,phone',
            'vhcReport.defects',
            'tasks.children.items', // Load task hierarchies if they exist
            'tasks.items',
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

        if (!$isGuestRoute && (! auth()->check() || ! $this->customerOwnsOrder($repairOrder, $request->user()))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:repair_tasks,id',
            'tasks.*.status' => 'required|in:approved,rejected',
            'customer_note' => 'nullable|string'
        ]);

        if ($repairOrder->status !== \App\Models\RepairOrder::STATUS_PENDING_APPROVAL) {
            return response()->json([
                'message' => 'Phiếu báo giá này đã được phản hồi hoặc không còn chờ duyệt.',
            ], 409);
        }

        $tasks = $validated['tasks'];
        $allApproved = true;
        $validTaskIds = [];

        foreach ($tasks as $taskData) {
            $task = \App\Models\RepairTask::find($taskData['id']);
            
            // Security check: ensure task belongs to this sequence
            if ($task->repair_order_id !== $repairOrder->id) {
                continue;
            }

            $task->update([
                'customer_approval_status' => $taskData['status']
            ]);
            $validTaskIds[] = $task->id;

            if ($taskData['status'] === 'rejected') {
                $allApproved = false;
            }
        }

        $validTasks = collect($tasks)->filter(fn ($taskData) => in_array((int) $taskData['id'], $validTaskIds, true));
        $approvedCount = $validTasks->where('status', 'approved')->count();
        $rejectedCount = $validTasks->where('status', 'rejected')->count();
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CUSTOMER_QUOTE_REVIEWED',
            'details' => "Đơn #{$repairOrder->id}: Khách hàng đồng ý {$approvedCount} hạng mục, từ chối {$rejectedCount} hạng mục.",
            'ip_address' => $request->ip(),
        ]);

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
                $repairOrder->update([
                    'status' => 'approved',
                    'quote_status' => 'approved',
                ]);
            } else {
                $hasApprovedTasks = $repairOrder->tasks()->where('customer_approval_status', 'approved')->exists();
                if ($hasApprovedTasks) {
                    $repairOrder->update([
                        'status' => 'approved',
                        'quote_status' => 'approved',
                    ]);
                } else {
                    $repairOrder->update([
                        'status' => 'cancelled',
                        'quote_status' => 'rejected',
                    ]);
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

    private function customerOwnsOrder(\App\Models\RepairOrder $repairOrder, ?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        $repairOrder->loadMissing('vehicle');

        return (int) $repairOrder->customer_id === (int) $user->id
            || (int) optional($repairOrder->vehicle)->user_id === (int) $user->id
            || (
                $user->phone
                && optional($repairOrder->vehicle)->owner_phone
                && preg_replace('/[^0-9]/', '', $user->phone) === preg_replace('/[^0-9]/', '', $repairOrder->vehicle->owner_phone)
            );
    }
}
