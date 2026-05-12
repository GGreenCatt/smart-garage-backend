<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $paidQuery = RepairOrder::query()
            ->with(['customer', 'vehicle', 'advisor'])
            ->where('payment_status', 'paid')
            ->whereBetween('updated_at', [$from, $to]);

        $repairOrders = (clone $paidQuery)
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        $paidOrders = (clone $paidQuery)->get(['id', 'total_amount', 'subtotal', 'discount_amount', 'tax_amount', 'updated_at']);
        $pendingRevenue = RepairOrder::where(function ($query) {
                $query->where('payment_status', '!=', 'paid')
                    ->orWhereNull('payment_status');
            })
            ->whereIn('status', [RepairOrder::STATUS_COMPLETED, RepairOrder::STATUS_APPROVED, RepairOrder::STATUS_IN_PROGRESS])
            ->sum('total_amount');

        $stats = [
            'total_revenue' => (float) $paidOrders->sum('total_amount'),
            'total_orders' => $paidOrders->count(),
            'average_order' => $paidOrders->count() > 0 ? (float) $paidOrders->avg('total_amount') : 0,
            'pending_revenue' => (float) $pendingRevenue,
        ];

        return view('admin.revenue.index', [
            'repairOrders' => $repairOrders,
            'stats' => $stats,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(RepairOrder $repairOrder)
    {
        $repairOrder->load([
            'customer',
            'vehicle',
            'advisor',
            'promotion',
            'tasks.items.itemable',
            'items.itemable',
            'items.repairTask',
        ]);

        $billableTasks = $repairOrder->tasks
            ->reject(fn ($task) => $task->customer_approval_status === 'rejected');
        $billableTaskIds = $billableTasks->pluck('id')->all();
        $billableItems = $repairOrder->items
            ->filter(fn ($item) => empty($item->repair_task_id) || in_array($item->repair_task_id, $billableTaskIds, true))
            ->values();
        $rejectedTasks = $repairOrder->tasks
            ->filter(fn ($task) => $task->customer_approval_status === 'rejected')
            ->values();
        $rejectedTaskIds = $rejectedTasks->pluck('id')->all();
        $rejectedItems = $repairOrder->items
            ->filter(fn ($item) => in_array($item->repair_task_id, $rejectedTaskIds, true))
            ->values();

        $laborTotal = (float) $billableTasks->sum('labor_cost');
        $itemsTotal = (float) $billableItems->sum('subtotal');

        return view('admin.revenue.show', [
            'repairOrder' => $repairOrder,
            'billableTasks' => $billableTasks->values(),
            'billableItems' => $billableItems,
            'rejectedTasks' => $rejectedTasks,
            'rejectedItems' => $rejectedItems,
            'rejectedTotal' => (float) $rejectedTasks->sum('labor_cost') + (float) $rejectedItems->sum('subtotal'),
            'laborTotal' => $laborTotal,
            'itemsTotal' => $itemsTotal,
        ]);
    }
}
