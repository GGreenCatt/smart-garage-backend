<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Show the Quote Builder Form
     */
    public function create($id)
    {
        if (auth()->user()?->isTechnician() && ! auth()->user()?->isAdmin() && ! auth()->user()?->isManager()) {
            abort(403, 'Kỹ thuật viên không có quyền tạo báo giá.');
        }

        $order = \App\Models\RepairOrder::with([
            'vehicle.user', 
            'advisor',
            'tasks.items', // if tasks have items
            'tasks.children.items', // eager load child tasks (proposed fixes)
            'items', // generic items on order
            'vhcReport.defects'
        ])->findOrFail($id);

        $services = \App\Models\Service::orderBy('name')->get();
        $parts = \App\Models\Part::orderBy('name')->get();

        $quoteWarnings = $this->quoteWarnings($order);

        return view('staff.quote.create', compact('order', 'services', 'parts', 'quoteWarnings'));
    }

    /**
     * Send a quote to the customer for approval with updated costs.
     */
    public function sendQuote(Request $request, \App\Models\RepairOrder $repairOrder)
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

        \Log::info("Quote Payload:", $request->all());
        // The frontend will send a 'tasks' array.
        // For existing tasks, it will send an array of 'proposed_fixes' inside.
        $tasksData = $request->input('tasks', []);
        
        foreach ($tasksData as $parentTaskId => $data) {
            $parentTask = $repairOrder->tasks()->find($parentTaskId);
            if (!$parentTask) continue;
            
            // Note: Parent task (Inspection) usually doesn't have a direct labor cost submitted here anymore
            
            if (isset($data['proposed_fixes']) && is_array($data['proposed_fixes'])) {
                foreach ($data['proposed_fixes'] as $fix) {
                    $childTask = null;
                    
                    // If this proposed fix comes from a VHC defect, try to update the existing auto-generated task
                    if (!empty($fix['task_id'])) {
                        $childTask = \App\Models\RepairTask::where('parent_id', $parentTaskId)
                            ->where('id', $fix['task_id'])
                            ->first();
                    }

                    if ($childTask) {
                        // For VHC defects, DO NOT change the title or type. This maintains the link to the 3D model.
                        // We store the proposed action in the description so the customer can still see what is proposed.
                        $proposedAction = $fix['title'] ?? '';
                        $originalDesc = $fix['description'] ?? '';
                        
                        $finalDesc = '';
                        if ($proposedAction) {
                            $finalDesc .= "Đề xuất sửa chữa: " . $proposedAction;
                        }
                        if ($originalDesc) {
                            $finalDesc .= ($finalDesc ? "\n" : "") . "Ghi chú báo lỗi: " . $originalDesc;
                        }

                        // Update existing defect task costs/details only
                        $childTask->update([
                            'severity' => $fix['severity'] ?? 'medium',
                            'description' => $finalDesc,
                            'labor_cost' => $fix['labor_cost'] ?? 0,
                            // Ensure it's marked as pending approval now that it has a quote
                            'status' => 'pending',
                            'customer_approval_status' => 'pending',
                        ]);
                    } else {
                        // Create the child RepairTask (Brand New Proposed Fix)
                        $childTask = \App\Models\RepairTask::create([
                            'repair_order_id' => $repairOrder->id,
                            'parent_id' => $parentTaskId,
                            'title' => $fix['title'] ?? 'Đề xuất sửa chữa', // Free text title from user
                            'type' => 'repair', // Since it's a fix
                            'status' => 'pending',
                            'customer_approval_status' => 'pending',
                            'service_id' => null, // No longer linked to predefined services
                            'labor_cost' => $fix['labor_cost'] ?? 0,
                            'severity' => $fix['severity'] ?? 'medium',
                            'description' => $fix['description'] ?? null,
                        ]);
                    }

                    // If parts (items) are provided, associate them
                    if (isset($fix['parts']) && is_array($fix['parts'])) {
                        // Clear existing parts for this task
                        \App\Models\RepairOrderItem::where('repair_task_id', $childTask->id)->delete();
                        
                        foreach ($fix['parts'] as $part) {
                            $partName = trim($part['name'] ?? '');
                            $partPrice = isset($part['price']) ? (float)$part['price'] : 0;
                            $partQty = isset($part['qty']) ? (int)$part['qty'] : 1;
                            
                            // Skip dummy parts or empty inputs from frontend
                            if (empty($partName) || ($partName === 'Phụ tùng kèm theo' && $partPrice <= 0)) {
                                continue;
                            }
                            
                            \App\Models\RepairOrderItem::create([
                                'repair_order_id' => $repairOrder->id,
                                'repair_task_id' => $childTask->id,
                                'name' => $partName,
                                'quantity' => $partQty,
                                'unit_price' => $partPrice,
                                'cost_price' => 0, // Optionally look up from db if exists
                                'subtotal' => $partPrice * $partQty,
                            ]);
                        }
                    }
                }
            }
        }

        // Require the order to have at least one task or have a VhcReport
        if ($repairOrder->tasks()->count() === 0 && !$repairOrder->vhcReport) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi báo giá khi chưa có task hoặc báo cáo VHC.',
            ], 400);
        }

        $repairOrder->loadMissing('vehicle');
        $customerId = $repairOrder->customer_id ?: optional($repairOrder->vehicle)->user_id;
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

        if ($customerId && !$repairOrder->customer_id) {
            $repairOrder->forceFill(['customer_id' => $customerId])->save();
        }

        $totalAmount = $repairOrder->tasks()->sum('labor_cost') + $repairOrder->items()->sum('subtotal');

        // Change the status of the repair order to indicate it's waiting for customer approval
        $repairOrder->update([
            'status' => 'pending_approval',
            'include_vhc' => $request->boolean('include_vhc', true),
            'total_amount' => $totalAmount,
        ]);
        
        // Also update any repair tasks that have a null customer_approval_status to 'pending'
        $repairOrder->tasks()
            ->whereNull('customer_approval_status')
            ->update(['customer_approval_status' => 'pending']);

        // Create a notification for the customer if one exists
        if ($customerId) {
            \App\Models\Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id' => $customerId,
                'type' => 'quote_ready',
                'data' => [
                    'title' => 'Báo giá dịch vụ mới',
                    'message' => "Báo giá sửa chữa cho xe {$repairOrder->vehicle->license_plate} đã sẵn sàng. Vui lòng kiểm tra và phê duyệt.",
                    'related_id' => $repairOrder->id,
                    'link' => '/customer/quote/' . $repairOrder->id // Adjust with real customer quote link if needed
                ],
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Publish the VHC report if it exists so the customer can see the 3D markers
        if ($repairOrder->vhcReport) {
            $repairOrder->vhcReport->update(['status' => 'published']);
        }

        // 3. Mark the Order as purely 'pending_approval' now
        $repairOrder->update([
            'status' => 'pending_approval',
            'quote_status' => 'sent',
            'quote_sent_at' => now(),
            'total_amount' => $totalAmount,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'STAFF_QUOTE_SENT',
            'details' => "Order #{$repairOrder->id}: Gửi báo giá cho khách, tổng tiền {$totalAmount}.",
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lưu báo giá và gửi yêu cầu phê duyệt thành công!',
            // 'download_url' => route('staff.quote.pdf', $repairOrder->id),
        ]);
    }

    /**
     * Show the detailed quote view for Staff.
     * This view is read-only and displays customer approval statuses.
     */
    public function show($id)
    {
        $order = \App\Models\RepairOrder::with([
            'vehicle.user', 
            'tasks.items', 
            'tasks.children.items' // Load children and their items (proposed fixes & parts)
        ])->findOrFail($id);

        return view('staff.quote.show', compact('order'));
    }

    private function quoteWarnings(\App\Models\RepairOrder $order): array
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
}
