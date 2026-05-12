<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\MaterialRequest;
use App\Models\Part;
use App\Models\RepairOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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
            'destination' => 'nullable|in:repair_order,inventory',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:150',
            'cost_price' => 'nullable|numeric|min:0|max:999999999',
            'unit_price' => 'nullable|numeric|min:0|max:999999999',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó');
        }

        if ($validated['status'] === 'rejected' && blank($validated['admin_note'] ?? null)) {
            return back()->with('error', 'Vui lòng nhập lý do từ chối');
        }

        $destination = $validated['destination'] ?? ($materialRequest->repair_order_id ? 'repair_order' : 'inventory');

        if ($validated['status'] === 'approved' && $destination === 'repair_order' && $materialRequest->repair_order_id) {
            if ((float) ($validated['unit_price'] ?? $materialRequest->unit_price ?? 0) <= 0) {
                return back()->with('error', 'Vui lòng nhập giá bán trước khi duyệt vật tư vào phiếu sửa chữa');
            }

            $order = RepairOrder::find($materialRequest->repair_order_id);
            if ($order && $order->isLockedForStaffChanges()) {
                return back()->with('error', 'Phiếu sửa chữa đã khóa nên không thể thêm vật tư');
            }
        }

        if ($validated['status'] === 'approved' && $destination === 'inventory') {
            if (blank($validated['sku'] ?? null)) {
                $validated['sku'] = $this->generateSku($materialRequest->part_name);
            }

            if ((float) ($validated['unit_price'] ?? $materialRequest->unit_price ?? 0) <= 0) {
                return back()->with('error', 'Vui lòng nhập giá bán trước khi nhập vật tư vào kho');
            }
        }

        DB::transaction(function () use ($materialRequest, $validated, $destination, $request) {
            $materialRequest->update([
                'status' => $validated['status'],
                'cost_price' => $validated['cost_price'] ?? $materialRequest->cost_price ?? 0,
                'unit_price' => $validated['unit_price'] ?? $materialRequest->unit_price ?? 0,
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            if ($validated['status'] === 'approved' && $destination === 'repair_order' && $materialRequest->repair_order_id) {
                $order = RepairOrder::with('promotion')->find($materialRequest->repair_order_id);

                if ($order && ! $order->isLockedForStaffChanges()) {
                    $item = $order->items()->create([
                        'name' => 'Vật tư ngoài: '.$materialRequest->part_name,
                        'quantity' => $materialRequest->quantity,
                        'cost_price' => $materialRequest->cost_price ?? 0,
                        'unit_price' => $materialRequest->unit_price ?? 0,
                        'subtotal' => $materialRequest->quantity * ($materialRequest->unit_price ?? 0),
                        'repair_task_id' => $materialRequest->repair_task_id,
                        'itemable_type' => null,
                        'itemable_id' => null,
                    ]);

                    $this->recalculateOrderTotal($order);

                    ActivityLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'MATERIAL_REQUEST_TO_REPAIR_ORDER',
                        'details' => "Đưa vật tư {$materialRequest->part_name} x{$materialRequest->quantity} vào phiếu {$order->track_id} (item #{$item->id})",
                        'ip_address' => $request->ip(),
                    ]);
                }
            }

            if ($validated['status'] === 'approved' && $destination === 'inventory') {
                $part = Part::firstOrCreate(
                    ['sku' => strtoupper($validated['sku'])],
                    [
                        'name' => $materialRequest->part_name,
                        'category' => $validated['category'] ?? 'Mua ngoài',
                        'purchase_price' => 0,
                        'selling_price' => $materialRequest->unit_price ?? 0,
                        'stock_quantity' => 0,
                        'min_stock' => 1,
                        'safety_stock' => 3,
                    ]
                );

                if (! $part->wasRecentlyCreated) {
                    $part->fill([
                        'name' => $part->name ?: $materialRequest->part_name,
                        'category' => $part->category ?: ($validated['category'] ?? 'Mua ngoài'),
                        'selling_price' => $materialRequest->unit_price ?? $part->selling_price,
                    ])->save();
                }

                $part->increment('stock_quantity', $materialRequest->quantity);

                InventoryTransaction::create([
                    'part_id' => $part->id,
                    'type' => 'in',
                    'quantity' => $materialRequest->quantity,
                    'user_id' => auth()->id(),
                    'reference' => 'MR-'.$materialRequest->id,
                    'note' => 'Nhập kho từ yêu cầu vật tư #'.$materialRequest->id,
                ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'MATERIAL_REQUEST_TO_INVENTORY',
                    'details' => "Nhập kho {$materialRequest->part_name} x{$materialRequest->quantity} từ yêu cầu #{$materialRequest->id}",
                    'ip_address' => $request->ip(),
                ]);
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
        $order->loadMissing(['tasks.items', 'items']);

        $taskTotal = $order->tasks
            ->reject(fn ($task) => $task->customer_approval_status === 'rejected')
            ->sum(fn ($task) => (float) ($task->labor_cost ?? 0) + (float) $task->items->sum('subtotal'));

        $billableTaskIds = $order->tasks
            ->reject(fn ($task) => $task->customer_approval_status === 'rejected')
            ->pluck('id')
            ->all();

        $standaloneItemsTotal = $order->items
            ->filter(fn ($item) => empty($item->repair_task_id) || ! in_array($item->repair_task_id, $billableTaskIds, true))
            ->sum('subtotal');

        $subtotal = (float) $taskTotal + (float) $standaloneItemsTotal;
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

    private function generateSku(string $partName): string
    {
        $base = Str::upper(Str::slug($partName, '-')) ?: 'PART';
        $base = Str::limit($base, 40, '');
        $sku = $base;
        $suffix = 1;

        while (Part::where('sku', $sku)->exists()) {
            $sku = $base.'-'.$suffix;
            $suffix++;
        }

        return $sku;
    }
}
