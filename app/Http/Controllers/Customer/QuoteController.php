<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RepairOrder;
use App\Models\RepairTask;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function show(Request $request, RepairOrder $repairOrder)
    {
        $isGuestRoute = str_starts_with($request->route()->getName(), 'guest.');

        if (! $isGuestRoute && (! auth()->check() || ! $this->customerOwnsOrder($repairOrder, $request->user()))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $repairOrder->load([
            'vehicle',
            'advisor:id,name,phone',
            'vhcReport.defects',
            'tasks.children.items',
            'tasks.items',
            'items',
        ]);

        return view('customer.quote', [
            'order' => $repairOrder,
        ]);
    }

    public function approveRejectTasks(Request $request, RepairOrder $repairOrder)
    {
        $isGuestRoute = str_starts_with($request->route()->getName(), 'guest.');

        if (! $isGuestRoute && (! auth()->check() || ! $this->customerOwnsOrder($repairOrder, $request->user()))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|integer',
            'tasks.*.status' => 'required|in:approved,rejected',
            'customer_note' => 'nullable|string|max:1000',
        ]);

        if ($repairOrder->status !== RepairOrder::STATUS_PENDING_APPROVAL || $repairOrder->quote_status !== 'sent') {
            return response()->json([
                'message' => 'Phiếu báo giá này đã được phản hồi hoặc không còn chờ duyệt.',
            ], 409);
        }

        $repairOrder->loadMissing(['tasks.items', 'advisor']);
        $quoteTasks = $this->quoteTasks($repairOrder);
        if ($quoteTasks->isEmpty()) {
            return response()->json([
                'message' => 'Phiếu báo giá này chưa có hạng mục cần duyệt.',
            ], 422);
        }

        $responses = collect($validated['tasks'])->keyBy(fn ($task) => (int) $task['id']);
        $quoteTaskIds = $quoteTasks->pluck('id')->map(fn ($id) => (int) $id)->values();

        $invalidIds = $responses->keys()
            ->map(fn ($id) => (int) $id)
            ->diff($quoteTaskIds);

        if ($invalidIds->isNotEmpty()) {
            return response()->json([
                'message' => 'Có hạng mục không thuộc phiếu báo giá này.',
            ], 422);
        }

        $missingIds = $quoteTaskIds->diff($responses->keys()->map(fn ($id) => (int) $id));
        if ($missingIds->isNotEmpty()) {
            return response()->json([
                'message' => 'Vui lòng phản hồi đầy đủ tất cả hạng mục báo giá.',
            ], 422);
        }

        $approvedCount = 0;
        $rejectedCount = 0;
        $approvedTotal = 0.0;

        DB::transaction(function () use (
            $repairOrder,
            $quoteTasks,
            $responses,
            $validated,
            &$approvedCount,
            &$rejectedCount,
            &$approvedTotal
        ) {
            foreach ($quoteTasks as $task) {
                $status = $responses->get($task->id)['status'];
                $task->update(['customer_approval_status' => $status]);

                if ($status === 'approved') {
                    $approvedCount++;
                    $approvedTotal += $this->taskTotal($task);
                } else {
                    $rejectedCount++;
                }
            }

            $update = [
                'customer_note' => $validated['customer_note'] ?? null,
                'total_amount' => $approvedTotal,
            ];

            if ($approvedCount > 0) {
                $update['status'] = RepairOrder::STATUS_APPROVED;
                $update['quote_status'] = 'approved';
            } else {
                $update['status'] = RepairOrder::STATUS_CANCELLED;
                $update['quote_status'] = 'rejected';
            }

            $repairOrder->update($update);
        });

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CUSTOMER_QUOTE_REVIEWED',
            'details' => "Đơn #{$repairOrder->id}: Khách hàng đồng ý {$approvedCount} hạng mục, từ chối {$rejectedCount} hạng mục.",
            'ip_address' => $request->ip(),
        ]);

        $advisor = User::find($repairOrder->advisor_id);
        if ($advisor) {
            NotificationService::send(
                $advisor,
                'quote_reviewed',
                'Phản hồi Báo Giá',
                "Khách hàng đã phản hồi báo giá cho đơn sửa chữa #{$repairOrder->id}.",
                route('staff.order.show', $repairOrder->id),
                'fas fa-file-invoice-dollar'
            );
        }

        return response()->json([
            'message' => 'Task approvals updated.',
            'repair_order' => $repairOrder->fresh(),
        ]);
    }

    private function quoteTasks(RepairOrder $repairOrder): Collection
    {
        $repairOrder->loadMissing('tasks.items');

        return $repairOrder->tasks
            ->whereNotNull('parent_id')
            ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty())
            ->values();
    }

    private function taskTotal(RepairTask $task): float
    {
        $task->loadMissing('items');

        return (float) ($task->labor_cost ?? 0) + (float) $task->items->sum('subtotal');
    }

    private function customerOwnsOrder(RepairOrder $repairOrder, ?User $user): bool
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
