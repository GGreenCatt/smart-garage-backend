<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Part;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function create($id)
    {
        if (auth()->user()?->isTechnician() && ! auth()->user()?->isAdmin() && ! auth()->user()?->isManager()) {
            abort(403, 'Kỹ thuật viên không có quyền tạo báo giá.');
        }

        $order = RepairOrder::with([
            'vehicle.user',
            'advisor',
            'tasks.items',
            'tasks.children.items',
            'items',
            'vhcReport.defects',
        ])->findOrFail($id);

        $services = Service::orderBy('name')->get();
        $parts = Part::orderBy('name')->get();
        $quoteWarnings = $this->quoteWarnings($order);

        return view('staff.quote.create', compact('order', 'services', 'parts', 'quoteWarnings'));
    }

    public function sendQuote(Request $request, RepairOrder $repairOrder)
    {
        if (auth()->user()?->isTechnician() && ! auth()->user()?->isAdmin() && ! auth()->user()?->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Kỹ thuật viên không có quyền gửi báo giá.',
            ], 403);
        }

        if ($repairOrder->isLockedForStaffChanges()) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn đã khóa, không thể gửi báo giá.',
            ], 409);
        }

        $repairOrder->loadMissing('vehicle');
        $customerId = $repairOrder->customer_id ?: optional($repairOrder->vehicle)->user_id;

        DB::transaction(function () use ($request, $repairOrder) {
            foreach ($request->input('tasks', []) as $parentTaskId => $data) {
                $parentTask = $repairOrder->tasks()->find($parentTaskId);
                if (! $parentTask) {
                    continue;
                }

                foreach (($data['proposed_fixes'] ?? []) as $fix) {
                    $childTask = $this->resolveOrCreateQuotedTask($repairOrder, $parentTask->id, $fix);
                    $this->syncTaskParts($repairOrder, $childTask, $fix['parts'] ?? []);
                }
            }
        });

        $repairOrder->refresh()->loadMissing([
            'vehicle',
            'customer',
            'items',
            'tasks.items',
            'tasks.children.items',
            'vhcReport.defects',
        ]);

        $criticalWarnings = collect($this->quoteWarnings($repairOrder))
            ->where('level', 'critical')
            ->pluck('message')
            ->values();

        if ($criticalWarnings->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => $criticalWarnings->implode(' '),
                'warnings' => $criticalWarnings,
            ], 422);
        }

        $quoteTasks = $this->quoteTasks($repairOrder);
        if ($quoteTasks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi báo giá khi chưa có hạng mục sửa chữa có chi phí để khách duyệt.',
            ], 422);
        }

        if ($customerId && ! $repairOrder->customer_id) {
            $repairOrder->forceFill(['customer_id' => $customerId])->save();
        }

        $quoteTasks
            ->filter(fn ($task) => $task->customer_approval_status !== 'pending')
            ->each(fn ($task) => $task->update(['customer_approval_status' => 'pending']));

        if ($repairOrder->vhcReport) {
            $repairOrder->vhcReport->update(['status' => 'published']);
        }

        $totalAmount = $this->quoteTotal($quoteTasks);
        $repairOrder->update([
            'status' => RepairOrder::STATUS_PENDING_APPROVAL,
            'quote_status' => 'sent',
            'quote_sent_at' => now(),
            'include_vhc' => $request->boolean('include_vhc', true),
            'total_amount' => $totalAmount,
        ]);

        if ($customerId) {
            Notification::create([
                'id' => Str::uuid(),
                'notifiable_type' => User::class,
                'notifiable_id' => $customerId,
                'type' => 'quote_ready',
                'data' => [
                    'title' => 'Báo giá dịch vụ mới',
                    'message' => "Báo giá sửa chữa cho xe {$repairOrder->vehicle->license_plate} đã sẵn sàng. Vui lòng kiểm tra và phê duyệt.",
                    'related_id' => $repairOrder->id,
                    'link' => route('customer.quote.show', $repairOrder->id, false),
                ],
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'STAFF_QUOTE_SENT',
            'details' => "Order #{$repairOrder->id}: Gửi báo giá cho khách, tổng tiền {$totalAmount}.",
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lưu báo giá và gửi yêu cầu phê duyệt thành công!',
        ]);
    }

    public function show($id)
    {
        $order = RepairOrder::with([
            'vehicle.user',
            'tasks.items',
            'tasks.children.items',
        ])->findOrFail($id);

        return view('staff.quote.show', compact('order'));
    }

    private function resolveOrCreateQuotedTask(RepairOrder $repairOrder, int $parentTaskId, array $fix): RepairTask
    {
        $childTask = null;

        if (! empty($fix['task_id'])) {
            $childTask = RepairTask::where('repair_order_id', $repairOrder->id)
                ->where('parent_id', $parentTaskId)
                ->where('id', $fix['task_id'])
                ->first();
        }

        if ($childTask) {
            $proposedAction = $fix['title'] ?? '';
            $originalDesc = $fix['description'] ?? '';
            $finalDesc = '';

            if ($proposedAction) {
                $finalDesc .= 'Đề xuất sửa chữa: '.$proposedAction;
            }
            if ($originalDesc) {
                $finalDesc .= ($finalDesc ? "\n" : '').'Ghi chú báo lỗi: '.$originalDesc;
            }

            $childTask->update([
                'severity' => $fix['severity'] ?? 'medium',
                'description' => $finalDesc,
                'labor_cost' => $fix['labor_cost'] ?? 0,
                'status' => 'pending',
                'customer_approval_status' => 'pending',
            ]);

            return $childTask;
        }

        return RepairTask::create([
            'repair_order_id' => $repairOrder->id,
            'parent_id' => $parentTaskId,
            'title' => $fix['title'] ?? 'Đề xuất sửa chữa',
            'type' => 'repair',
            'status' => 'pending',
            'customer_approval_status' => 'pending',
            'service_id' => null,
            'labor_cost' => $fix['labor_cost'] ?? 0,
            'severity' => $fix['severity'] ?? 'medium',
            'description' => $fix['description'] ?? null,
        ]);
    }

    private function syncTaskParts(RepairOrder $repairOrder, RepairTask $childTask, array $parts): void
    {
        RepairOrderItem::where('repair_task_id', $childTask->id)->delete();

        foreach ($parts as $part) {
            $partName = trim($part['name'] ?? '');
            $partPrice = isset($part['price']) ? (float) $part['price'] : 0;
            $partQty = isset($part['qty']) ? max((int) $part['qty'], 1) : 1;

            if ($partName === '' || ($partName === 'Phụ tùng kèm theo' && $partPrice <= 0)) {
                continue;
            }

            RepairOrderItem::create([
                'repair_order_id' => $repairOrder->id,
                'repair_task_id' => $childTask->id,
                'name' => $partName,
                'quantity' => $partQty,
                'unit_price' => $partPrice,
                'cost_price' => 0,
                'subtotal' => $partPrice * $partQty,
            ]);
        }
    }

    private function quoteWarnings(RepairOrder $order): array
    {
        $order->loadMissing(['vehicle.user', 'customer', 'items', 'tasks.items', 'tasks.children', 'vhcReport.defects']);

        $warnings = [];
        if (! $order->customer_id && ! optional($order->vehicle)->user_id) {
            $warnings[] = ['level' => 'critical', 'message' => 'Đơn chưa gắn khách hàng, không thể gửi báo giá.'];
        }

        if (! $order->vehicle_id || ! $order->vehicle) {
            $warnings[] = ['level' => 'critical', 'message' => 'Đơn chưa gắn xe, không thể gửi báo giá.'];
        }

        if ($order->tasks->isEmpty() && ! $order->vhcReport) {
            $warnings[] = ['level' => 'critical', 'message' => 'Đơn chưa có hạng mục kiểm tra hoặc dữ liệu VHC.'];
        }

        $tasksMissingPrice = $order->tasks
            ->whereNull('parent_id')
            ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) <= 0 && $task->children->isEmpty())
            ->count();

        if ($tasksMissingPrice > 0) {
            $warnings[] = [
                'level' => 'warning',
                'code' => 'missing_task_quote',
                'message' => "{$tasksMissingPrice} hạng mục kiểm tra chưa có đề xuất sửa chữa hoặc chi phí.",
            ];
        }

        if ($order->items->contains(fn ($item) => (float) ($item->unit_price ?? 0) <= 0)) {
            $warnings[] = ['level' => 'warning', 'message' => 'Có vật tư chưa có giá bán.'];
        }

        if (! $order->vhcReport && $order->tasks->contains(fn ($task) => $task->type === 'vhc')) {
            $warnings[] = ['level' => 'warning', 'message' => 'Có hạng mục kiểm tra 3D/VHC nhưng chưa có dữ liệu VHC.'];
        }

        return $warnings;
    }

    private function quoteTasks(RepairOrder $order): Collection
    {
        $order->loadMissing('tasks.items');

        return $order->tasks
            ->whereNotNull('parent_id')
            ->filter(fn ($task) => (float) ($task->labor_cost ?? 0) > 0 || $task->items->isNotEmpty())
            ->values();
    }

    private function quoteTotal(Collection $quoteTasks): float
    {
        return (float) $quoteTasks->sum(
            fn ($task) => (float) ($task->labor_cost ?? 0) + (float) $task->items->sum('subtotal')
        );
    }
}
