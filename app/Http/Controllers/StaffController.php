<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RepairTask;
use App\Models\RepairOrder;
use App\Models\Vehicle;
use App\Models\Part;
use App\Models\WorkShift;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
use App\Models\RepairItem;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffController extends Controller
{
    public function __construct(
        protected \App\Services\Staff\VehicleService $vehicleService
    ) {}

    public function dashboard()
    {
        // Kanban Board Data: Fetch Orders instead of Tasks
        // We need 'waiting' (pending), 'inProgress', 'ready' (completed)
        
        $ordersQuery = RepairOrder::with(['vehicle', 'vehicle.user', 'advisor', 'items'])
            ->withCount('items');

        if ($search = trim((string) request('q'))) {
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('track_id', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('license_plate', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('owner_phone', 'like', "%{$search}%")
                            ->orWhere('owner_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = request('status')) {
            $ordersQuery->where('status', $status);
        }

        if ($advisorId = request('advisor_id')) {
            $ordersQuery->where('advisor_id', $advisorId);
        }

        if ($dateFrom = request('date_from')) {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = request('date_to')) {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $ordersQuery->latest()->get();
        $advisors = User::whereIn('role', ['staff', 'technician', 'manager'])
            ->orderBy('name')
            ->get();

        // Fetch tasks separately to calculate progress manually
        $allTasks = \App\Models\RepairTask::with(['children.items', 'items', 'mechanic'])->whereIn('repair_order_id', $orders->pluck('id'))->get();

        // Manual mapping for progress since relationship might be missing in model file read earlier
        $orders->transform(function ($order) use ($allTasks) {
            $allOrderTasks = $allTasks->where('repair_order_id', $order->id);
            $orderTasks = $allOrderTasks->reject(fn ($task) => $task->customer_approval_status === 'rejected');
            $total = $orderTasks->count();
            $completed = $orderTasks->where('status', 'completed')->count();
            
            $order->progress_percent = $total > 0 ? round(($completed / $total) * 100) : 5; // Default 5% for "Started"
            
            // Collect unique mechanic names
            $mechanicNames = $orderTasks->pluck('mechanic.name')->filter()->unique()->values()->all();
            $order->mechanics_display = !empty($mechanicNames) ? implode(', ', $mechanicNames) : 'ChÆ°a phÃ¢n cÃ´ng';

            // Status Label
            if ($order->status == 'pending') $order->status_label = 'Chá» xá»­ lÃ½';
            elseif (in_array($order->status, ['in_progress', 'pending_approval', 'approved'])) $order->status_label = 'Äang xá»­ lÃ½';
            elseif ($order->status == 'completed') $order->status_label = 'Sáºµn sÃ ng';
            else $order->status_label = $order->status === 'cancelled' ? 'ÄÃ£ há»§y' : ucfirst($order->status);

            $order->has_rejected_tasks = $allOrderTasks->where('customer_approval_status', 'rejected')->isNotEmpty();
            $order->approved_tasks_count = $orderTasks->where('customer_approval_status', 'approved')->count();
            $order->status_label = match ($order->status) {
                RepairOrder::STATUS_PENDING => 'Chá» tiáº¿p nháº­n',
                RepairOrder::STATUS_IN_PROGRESS => 'Äang kiá»ƒm tra',
                RepairOrder::STATUS_PENDING_APPROVAL => 'Chá» khÃ¡ch duyá»‡t',
                RepairOrder::STATUS_APPROVED => 'KhÃ¡ch Ä‘Ã£ duyá»‡t',
                RepairOrder::STATUS_COMPLETED => $order->payment_status === 'paid' ? 'ÄÃ£ thanh toÃ¡n' : 'Chá» thanh toÃ¡n',
                RepairOrder::STATUS_CANCELLED => 'ÄÃ£ há»§y',
                default => ucfirst((string) $order->status),
            };

            return $order;
        });

        $waiting = $orders->where('status', RepairOrder::STATUS_PENDING);
        $inProgress = $orders->where('status', RepairOrder::STATUS_IN_PROGRESS);
        $pendingApproval = $orders->where('status', RepairOrder::STATUS_PENDING_APPROVAL);
        $approved = $orders->where('status', RepairOrder::STATUS_APPROVED);
        $ready = $orders->where('status', RepairOrder::STATUS_COMPLETED);
        
        $allVehicles = \App\Models\Vehicle::all();
        
        // Determine Selected Order
        $selectedId = request('order_id');
        $selectedOrder = null;
        if ($selectedId) {
            $selectedOrder = $orders->firstWhere('id', $selectedId);
        }
        if (!$selectedOrder) {
            $selectedOrder = $approved->first() ?? $pendingApproval->first() ?? $inProgress->first() ?? $waiting->first() ?? $ready->first();
        }
        
        $pendingTasks = $allTasks->where('status', 'pending');
        $progressTasks = $allTasks->whereIn('status', ['in_progress', 'completed']);
        $orderActivities = $selectedOrder ? $this->orderActivities($selectedOrder) : collect();

        if (request()->ajax()) {
            $currentTasks = $selectedOrder ? $allTasks->where('repair_order_id', $selectedOrder->id) : collect();
            $orderActivities = $selectedOrder ? $this->orderActivities($selectedOrder) : collect();
            return view('staff.partials.order_details', compact('selectedOrder', 'currentTasks', 'orderActivities'));
        }

        return view('staff.dashboard', compact('waiting', 'inProgress', 'pendingApproval', 'approved', 'ready', 'allVehicles', 'allTasks', 'pendingTasks', 'progressTasks', 'advisors', 'selectedOrder', 'orderActivities'));
    }

    // Helper for customer vehicles
    public function getVehiclesJson($id)
    {
        $vehicles = $this->vehicleService->getByCustomer($id);
        return response()->json($vehicles);
    }

    public function getVehiclesJsonByPhone($phone)
    {
        $vehicles = $this->vehicleService->getByPhone($phone);
        return response()->json($vehicles);
    }

    public function checkCustomer(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->query('phone'));
        $user = \App\Models\User::where('phone', $phone)->first();
        
        if ($user) {
            return response()->json(['exists' => true, 'name' => $user->name]);
        }
        
        return response()->json(['exists' => false]);
    }
    
    public function storeVehicle(\Illuminate\Http\Request $request)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return response()->json(['success' => false, 'message' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n tiáº¿p nháº­n xe má»›i.'], 403);
        }

        // Validation (Simulated for speed, add proper rules later)
        $validated = $request->validate([
            'license_plate' => 'required|string|max:20',
            'model' => 'required|string',
            'type' => 'required|string',
            'owner_name' => 'required|string',
            'owner_phone' => 'required|string',
        ]);

        // 1. Create Customer (if needed) & Vehicle via Service
        $vehicle = $this->vehicleService->store($validated);

        // 2. Create Repair Order (Intake)
        $order = \App\Models\RepairOrder::create([
            'customer_id' => $vehicle->user_id,
            'vehicle_id' => $vehicle->id,
            'service_type' => 'Full Service (Intake)',
            'status' => 'pending',
            'advisor_id' => auth()->id() ?? 1, // Fallback to 1 for demo
            'start_time' => now(),
            'track_id' => strtoupper(uniqid('RO-')), // Generate unique Track ID
        ]);

        // 3. Create Selected Inspection Tasks
        $inspectionOptions = $request->input('inspection_options', []);
        
        if (!empty($inspectionOptions['general'])) {
            $title = !empty($inspectionOptions['use_3d']) ? 'Kiá»ƒm tra tá»•ng quÃ¡t (3D)' : 'Kiá»ƒm tra tá»•ng quÃ¡t';
            \App\Models\RepairTask::create([
                'repair_order_id' => $order->id,
                'title' => $title,
                'type' => !empty($inspectionOptions['use_3d']) ? 'vhc' : 'general',
                'status' => 'pending',
            ]);
        }

        if (!empty($inspectionOptions['cabin'])) {
            \App\Models\RepairTask::create([
                'repair_order_id' => $order->id,
                'title' => 'Kiá»ƒm tra bÃªn trong khoang lÃ¡i',
                'type' => 'general',
                'status' => 'pending',
            ]);
        }

        if (!empty($inspectionOptions['engine'])) {
            \App\Models\RepairTask::create([
                'repair_order_id' => $order->id,
                'title' => 'Kiá»ƒm tra Ä‘á»™ng cÆ¡',
                'type' => 'general',
                'status' => 'pending',
            ]);
        }

        $this->logOrderActivity($order, 'STAFF_ORDER_INTAKE', 'Tiáº¿p nháº­n xe vÃ  táº¡o order ban Ä‘áº§u.');

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle,
            'order' => $order
        ]);
    }
    public function inspection($id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        
        $backUrl = route('staff.dashboard');
        if (request('order_id')) {
            $backUrl = route('staff.dashboard', ['order_id' => request('order_id')]);
        }

        return view('staff.vehicle.inspection', compact('vehicle', 'backUrl'));
    }

    public function getVhcData($id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        // Find active Repair Order (not completed)
        $order = \App\Models\RepairOrder::where('vehicle_id', $vehicle->id)
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->first();

        if (!$order) {
            return response()->json(['defects' => []]);
        }

        $report = \App\Models\VhcReport::where('repair_order_id', $order->id)->first();
        if (!$report) {
            return response()->json(['defects' => []]);
        }

        return response()->json(['defects' => $report->defects]);
    }

    public function saveVhcData(Request $request, $id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        
        $orderId = $request->input('order_id');
        
        if ($orderId) {
            $order = \App\Models\RepairOrder::where('id', $orderId)
                        ->where('vehicle_id', $vehicle->id)
                        ->firstOrFail();
        } else {
            $order = \App\Models\RepairOrder::where('vehicle_id', $vehicle->id)
                        ->where('status', '!=', 'completed')
                        ->latest()
                        ->firstOrFail();
        }

        if ($order->isLockedForStaffChanges() || in_array($order->status, ['pending_approval', 'approved'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'KhÃ´ng thá»ƒ chá»‰nh sá»­a VHC sau khi bÃ¡o giÃ¡ Ä‘Ã£ Ä‘Æ°á»£c gá»­i hoáº·c Ä‘Æ¡n Ä‘Ã£ khÃ³a.'
            ], 409);
        }

        $report = \App\Models\VhcReport::firstOrCreate(['repair_order_id' => $order->id]);
        
        // Update Status
        $status = $request->input('status', 'draft');
        $report->status = $status;
        $report->save();

        // 1. Find or Create UNIQUE Parent VHC Task (Reuse existing if available)
        // Improved Logic: Search by Title OR Type to catch existing tasks created without type
        // ALSO: Check for "Kiá»ƒm tra tá»•ng quÃ¡t (VHC)" which seems to be an alternative title in the system
        $parentTask = \App\Models\RepairTask::where('repair_order_id', $order->id)
            ->where(function ($query) {
                $query->where('type', 'vhc')
                      ->orWhere('title', 'Kiá»ƒm tra tá»•ng quÃ¡t (3D)')
                      ->orWhere('title', 'Kiá»ƒm tra tá»•ng quÃ¡t (VHC)');
            })
            ->first();

        if (!$parentTask) {
            $parentTask = \App\Models\RepairTask::create([
                'repair_order_id' => $order->id,
                'type' => 'vhc',
                'title' => 'Kiá»ƒm tra tá»•ng quÃ¡t (3D)', // Standardized Title
                'status' => 'pending'
            ]);
        }
        
        // Ensure type IS set correctly if found by title (self-healing for legacy)
        if ($parentTask->type !== 'vhc') {
            $parentTask->update(['type' => 'vhc']);
        }

        // Sync VHC defects and their pre-quote child tasks together.
        $report->defects()->delete();
        $parentTask->children()->where('type', 'defect')->delete();

        foreach ($request->input('defects', []) as $d) {
            $title = $d['part'] ?? 'Unknown';
            $description = $d['description'] ?? '';
            $severity = $d['severity'] ?? 'medium';

            $report->defects()->create([
                'title' => $title,
                'description' => $description,
                'type' => 'general',
                'severity' => $severity,
                'pos_x' => $d['pos']['x'],
                'pos_y' => $d['pos']['y'],
                'pos_z' => $d['pos']['z'],
                'images' => []
            ]);

            \App\Models\RepairTask::create([
                    'repair_order_id' => $order->id,
                    'title' => $title,
                    'description' => $description,
                    'severity' => $severity,
                    'parent_id' => $parentTask->id,
                    'status' => 'pending',
                    'type' => 'defect',
            ]);
        }

        $this->logOrderActivity($order, 'STAFF_VHC_SAVED', 'LÆ°u VHC ' . $status . ' vá»›i ' . count($request->input('defects', [])) . ' defect.');

        return response()->json(['success' => true]);
    }
    public function fetchVhcData($id, \Illuminate\Http\Request $request)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        
        $orderId = $request->input('order_id');
        
        if ($orderId) {
            $order = \App\Models\RepairOrder::where('id', $orderId)
                        ->where('vehicle_id', $vehicle->id)
                        ->first();
        } else {
            // Find latest active order
            $order = \App\Models\RepairOrder::where('vehicle_id', $vehicle->id)
                        ->where('status', '!=', 'completed')
                        ->latest()
                        ->first();
        }

        if (!$order) {
            return response()->json(['defects' => []]);
        }

        $report = \App\Models\VhcReport::with('defects')->where('repair_order_id', $order->id)->first();

        if (!$report) {
            return response()->json(['defects' => []]);
        }

        return response()->json([
            'defects' => $report->defects,
            'status' => $report->status
        ]);
    }

    public function showOrder($id)
    {
        return redirect()->route('staff.dashboard', ['order_id' => $id]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return response()->json(['success' => false, 'message' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n cáº­p nháº­t tráº¡ng thÃ¡i Ä‘Æ¡n.'], 403);
        }

        $order = \App\Models\RepairOrder::with(['items', 'tasks.items', 'promotion'])->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        if ($order->payment_status === 'paid' && $validated['status'] !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'ÄÆ¡n Ä‘Ã£ thanh toÃ¡n, khÃ´ng thá»ƒ má»Ÿ láº¡i tráº¡ng thÃ¡i.'
            ], 409);
        }

        if (in_array($order->status, ['completed', 'cancelled'], true) && $validated['status'] !== $order->status) {
            return response()->json([
                'success' => false,
                'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ cáº­p nháº­t tráº¡ng thÃ¡i.'
            ], 409);
        }

        if ($validated['status'] === 'in_progress' && $order->status === 'pending_approval') {
            return response()->json([
                'success' => false,
                'message' => 'ÄÆ¡n Ä‘ang chá» khÃ¡ch duyá»‡t bÃ¡o giÃ¡, chÆ°a thá»ƒ chuyá»ƒn sang thi cÃ´ng.'
            ], 409);
        }

        if ($validated['status'] === 'completed') {
            if (! in_array($order->status, ['approved', 'in_progress'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chá»‰ cÃ³ thá»ƒ hoÃ n thÃ nh Ä‘Æ¡n sau khi khÃ¡ch Ä‘Ã£ duyá»‡t bÃ¡o giÃ¡ vÃ  cÃ´ng viá»‡c Ä‘Ã£ Ä‘Æ°á»£c xá»­ lÃ½.'
                ], 409);
            }

            $hasPendingTasks = $order->tasks()
                ->where('status', '!=', 'completed')
                ->where(function ($query) {
                    $query->whereNull('customer_approval_status')
                        ->orWhere('customer_approval_status', '!=', 'rejected');
                })
                ->exists();
            if ($hasPendingTasks) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lÃ²ng hoÃ n thÃ nh táº¥t cáº£ cÃ´ng viá»‡c trÆ°á»›c khi Ä‘Ã³ng Ä‘Æ¡n.'
                ], 422);
            }
        }

        $updateData = ['status' => $validated['status']];
        if ($request->filled('estimated_end_time')) {
            $updateData['expected_completion_date'] = $request->input('estimated_end_time');
        }

        $oldStatus = $order->status;
        $order->update($updateData);
        $this->logOrderActivity(
            $order,
            'STAFF_ORDER_STATUS_UPDATED',
            'Äá»•i tráº¡ng thÃ¡i tá»« ' . $this->friendlyOrderStatus($oldStatus) . ' sang ' . $this->friendlyOrderStatus($validated['status']) . '.'
        );

        if ($validated['status'] === 'in_progress') {
            // Create or open chat session for this job
            \App\Models\ChatSession::updateOrCreate(
                ['repair_order_id' => $order->id],
                [
                    'customer_id' => $order->customer_id,
                    'status' => 'open',
                    'updated_at' => now()
                ]
            );
        }

        // Un-assign tasks and reset status if reverted back to pending
        if ($validated['status'] === 'pending') {
            \App\Models\RepairTask::where('repair_order_id', $order->id)
                ->update([
                    'mechanic_id' => null,
                    'status' => 'pending'
                ]);
        }

        // Notify Customer about status change
        if ($order->customer_id && in_array($validated['status'], ['in_progress', 'completed'])) {
            $customer = \App\Models\User::find($order->customer_id);
            if ($customer) {
                $statusText = $validated['status'] === 'completed' ? 'Ä‘Ã£ hoÃ n thÃ nh' : 'Ä‘ang Ä‘Æ°á»£c tiáº¿n hÃ nh';
                $iconColor = $validated['status'] === 'completed' ? 'text-green-500' : 'text-blue-500';
                $icon = $validated['status'] === 'completed' ? 'fas fa-check-circle' : 'fas fa-tools';
                
                \App\Services\NotificationService::send(
                    $customer,
                    'order_status_updated',
                    'Cáº­p nháº­t tráº¡ng thÃ¡i sá»­a chá»¯a',
                    "ÄÆ¡n sá»­a chá»¯a #{$order->id} cá»§a báº¡n {$statusText}.",
                    route('customer.dashboard'),
                    "{$icon} {$iconColor}"
                );

                // Close chat session if completed
                if ($validated['status'] === 'completed') {
                    \App\Models\ChatSession::where('repair_order_id', $order->id)
                        ->update(['status' => 'closed', 'updated_at' => now()]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function storeTask(Request $request, $orderId)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return response()->json(['success' => false, 'message' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n thÃªm task bÃ¡o giÃ¡.'], 403);
        }

        $order = \App\Models\RepairOrder::findOrFail($orderId);
        if ($order->isLockedForStaffChanges()) {
            return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ thÃªm cÃ´ng viá»‡c.'], 409);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:repair_tasks,id',
            'type' => 'required|string'
        ]);

        $task = $order->tasks()->create([
            'repair_order_id' => $orderId,
            'title' => $validated['title'],
            'parent_id' => $validated['parent_id'],
            'type' => $validated['type'],
            'status' => 'pending'
        ]);

        $this->logOrderActivity($order, 'STAFF_TASK_CREATED', "ThÃªm task {$task->title}.");

        return response()->json(['success' => true]);
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = \App\Models\RepairTask::with(['children', 'repairOrder'])->findOrFail($id);
        if ($task->repairOrder->isLockedForStaffChanges()) {
            return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ cáº­p nháº­t cÃ´ng viá»‡c.'], 409);
        }
        if ($task->customer_approval_status === 'rejected') {
            return response()->json(['success' => false, 'message' => 'KhÃ¡ch hÃ ng Ä‘Ã£ tá»« chá»‘i cÃ´ng viá»‡c nÃ y.'], 409);
        }

        $status = $request->input('status');

        // Constraint: Parent cannot be completed if approved/non-quoted children are not done.
        $hasIncompleteApprovedChildren = $task->children()
            ->where('status', '!=', 'completed')
            ->where(function ($query) {
                $query->whereNull('customer_approval_status')
                    ->orWhere('customer_approval_status', '!=', 'rejected');
            })
            ->exists();

        if ($status === 'completed' && $hasIncompleteApprovedChildren) {
             return response()->json([
                 'success' => false, 
                 'message' => 'Vui lÃ²ng hoÃ n thÃ nh táº¥t cáº£ nhiá»‡m vá»¥ con trÆ°á»›c!'
             ], 400);
        }

        $oldStatus = $task->status;
        $task->update([
            'status' => $status
        ]);
        
        // Touch the order to update timestamp
        $task->repairOrder->touch();
        $this->logOrderActivity($task->repairOrder, 'STAFF_TASK_STATUS_UPDATED', "Cáº­p nháº­t task {$task->title} tá»« {$oldStatus} sang {$status}.");
        
        return response()->json(['success' => true]);
    }


    public function inventory(Request $request)
    {
        $query = \App\Models\Part::query();

        // Stats for Dashboard
        $totalParts = \App\Models\Part::count();
        $lowStockCount = \App\Models\Part::whereColumn('stock_quantity', '<=', 'min_stock')->count();
        $pendingRequestsCount = \App\Models\MaterialRequest::where('staff_id', \Illuminate\Support\Facades\Auth::id())
            ->where('status', 'pending')
            ->count();
        $categories = \App\Models\Part::whereNotNull('category')->distinct()->pluck('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->get('filter') === 'low_stock') {
            $query->whereColumn('stock_quantity', '<=', 'min_stock');
        }

        $parts = $query->latest()->paginate(15);
        
        if (request()->ajax()) {
            return view('staff.inventory.partials._grid', compact('parts'))->render();
        }

        return view('staff.inventory.index', compact(
            'parts', 
            'totalParts', 
            'lowStockCount', 
            'pendingRequestsCount',
            'categories'
        ));
    }

    public function index(Request $request)
    {
        $query = User::where('role', 'customer');
        
        if($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->withCount('vehicles')->latest()->paginate(12);
        return view('staff.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = User::with(['vehicles.repairOrders' => function($q) {
            $q->latest();
        }])->where('role', 'customer')->findOrFail($id);
        
        $totalSpent = $customer->repairOrders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
            
        return view('staff.customers.show', compact('customer', 'totalSpent'));
    }

    public function create()
    {
        return view('staff.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'role' => 'customer',
            'role_id' => \App\Models\Role::where('slug', 'customer')->value('id')
        ]);

        return redirect()->route('staff.customers.show', $user->id)->with('success', 'KhÃ¡ch hÃ ng má»›i Ä‘Ã£ Ä‘Æ°á»£c táº¡o thÃ nh cÃ´ng!');
    }

    public function edit($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        return view('staff.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$id,
            'phone' => 'required|string|unique:users,phone,'.$id,
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('staff.customers.show', $id)->with('success', 'ThÃ´ng tin khÃ¡ch hÃ ng Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t.');
    }

    public function destroy($id)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return back()->withErrors(['error' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n xÃ³a khÃ¡ch hÃ ng.']);
        }

        $customer = User::where('role', 'customer')->findOrFail($id);

        if ($customer->repairOrders()->exists() || $customer->vehicles()->whereHas('repairOrders')->exists()) {
            return back()->withErrors(['error' => 'KhÃ´ng thá»ƒ xÃ³a khÃ¡ch hÃ ng Ä‘Ã£ cÃ³ xe hoáº·c lá»‹ch sá»­ sá»­a chá»¯a.']);
        }

        $customerName = $customer->name;
        $customer->vehicles()->delete();
        $customer->delete();

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'STAFF_CUSTOMER_DELETED',
            'details' => "XÃ³a khÃ¡ch hÃ ng {$customerName}.",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.customers.index')->with('success', 'KhÃ¡ch hÃ ng Ä‘Ã£ Ä‘Æ°á»£c xÃ³a.');
    }

    public function editVehicle($id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        return view('staff.vehicles.edit', compact('vehicle'));
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        
        $validated = $request->validate([
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,'.$id,
            'model' => 'required|string',
            'type' => 'required|string', // Sedan, SUV, etc.
            'vin' => 'nullable|string',
            'year' => 'nullable|integer|min:1900|max:'.(date('Y')+1),
            'color' => 'nullable|string'
        ]);

        $this->vehicleService->update($vehicle, $validated);

        // Redirect back to customer detail
        return redirect()->route('staff.customers.show', $vehicle->user_id ?? $request->input('redirect_id', 1)) // Fallback if no user linked
                         ->with('success', 'ThÃ´ng tin xe Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t.');
    }

    public function destroyVehicle($id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $customerId = $vehicle->user_id; // Save needed ID before delete

        if ($vehicle->repairOrders()->exists()) {
            return redirect()
                ->route('staff.customers.show', $customerId)
                ->withErrors(['error' => 'KhÃ´ng thá»ƒ xÃ³a xe Ä‘Ã£ cÃ³ lá»‹ch sá»­ sá»­a chá»¯a.']);
        }
        
        // Log Activity
        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_VEHICLE',
            'details' => "Deleted vehicle {$vehicle->license_plate} ({$vehicle->model}) of customer ID: {$customerId}",
            'ip_address' => request()->ip()
        ]);

        $this->vehicleService->delete($vehicle);

        return redirect()->route('staff.customers.show', $customerId)
                         ->with('success', 'Xe Ä‘Ã£ Ä‘Æ°á»£c xÃ³a thÃ nh cÃ´ng khá»i há»‡ thá»‘ng.');
    }

    public function profile()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function processPayment(Request $request, $id)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return response()->json(['success' => false, 'message' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n thanh toÃ¡n Ä‘Æ¡n.'], 403);
        }

        $order = \App\Models\RepairOrder::with(['items', 'tasks.items', 'promotion'])->findOrFail($id);

        if ($order->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Xe chÆ°a hoÃ n thÃ nh, khÃ´ng thá»ƒ thanh toÃ¡n.',
            ], 400);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'ÄÆ¡n sá»­a chá»¯a nÃ y Ä‘Ã£ Ä‘Æ°á»£c thanh toÃ¡n.',
            ], 400);
        }

        $paymentMethod = $request->input('payment_method', 'cash');
        $couponCode = strtoupper(trim((string) $request->input('coupon_code', '')));
        $amounts = $this->paymentAmounts($order, $couponCode);
        if (! $amounts['success']) {
            return response()->json([
                'success' => false,
                'message' => $amounts['message'],
            ], 422);
        }

        $order->update([
            'subtotal' => $amounts['base_amount'],
            'discount_amount' => $amounts['discount_amount'],
            'promotion_id' => $amounts['promotion']?->id ?: $order->promotion_id,
            'total_amount' => $amounts['total_amount'],
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
        ]);

        if ($couponCode !== '' && $amounts['promotion']) {
            $amounts['promotion']->increment('used_count');
        }

        $paymentDetails = 'XÃ¡c nháº­n thanh toÃ¡n báº±ng ' . $order->payment_method . ', tá»•ng thu ' . number_format($amounts['total_amount'], 0, ',', '.') . 'Ä‘.';
        if ($amounts['discount_amount'] > 0 && $amounts['promotion']) {
            $paymentDetails .= ' Ãp dá»¥ng mÃ£ giáº£m giÃ¡ ' . $amounts['promotion']->code . ', giáº£m ' . number_format($amounts['discount_amount'], 0, ',', '.') . 'Ä‘.';
        }
        $this->logOrderActivity($order, 'STAFF_PAYMENT_RECEIVED', $paymentDetails);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toÃ¡n thÃ nh cÃ´ng',
            'total_amount' => $amounts['total_amount'],
            'discount_amount' => $amounts['discount_amount'],
        ]);
    }

    private function paymentAmounts(\App\Models\RepairOrder $order, ?string $couponCode = null): array
    {
        $couponCode = strtoupper(trim((string) $couponCode));
        $baseAmount = $this->paymentBaseAmount($order);
        $discountAmount = (float) ($order->discount_amount ?? 0);
        $promotion = $order->promotion;

        if ($couponCode !== '') {
            $promotion = \App\Models\Promotion::whereRaw('UPPER(code) = ?', [$couponCode])->first();

            if (! $promotion) {
                return ['success' => false, 'message' => 'MÃ£ giáº£m giÃ¡ khÃ´ng tá»“n táº¡i.'];
            }

            if (! $promotion->isValid()) {
                return ['success' => false, 'message' => 'MÃ£ giáº£m giÃ¡ Ä‘Ã£ háº¿t háº¡n hoáº·c khÃ´ng kháº£ dá»¥ng.'];
            }

            if ($promotion->customer_id && (int) $promotion->customer_id !== (int) $order->customer_id) {
                return ['success' => false, 'message' => 'MÃ£ giáº£m giÃ¡ nÃ y khÃ´ng Ã¡p dá»¥ng cho khÃ¡ch hÃ ng cá»§a Ä‘Æ¡n nÃ y.'];
            }

            if ($promotion->vehicle_id && (int) $promotion->vehicle_id !== (int) $order->vehicle_id) {
                return ['success' => false, 'message' => 'MÃ£ giáº£m giÃ¡ nÃ y khÃ´ng Ã¡p dá»¥ng cho xe cá»§a Ä‘Æ¡n nÃ y.'];
            }

            $discountAmount = $promotion->type === 'fixed'
                ? (float) $promotion->value
                : $baseAmount * ((float) $promotion->value / 100);
        }

        $discountAmount = min($discountAmount, $baseAmount);

        return [
            'success' => true,
            'base_amount' => $baseAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $baseAmount - $discountAmount + (float) ($order->tax_amount ?? 0)),
            'promotion' => $promotion,
        ];
    }

    private function paymentBaseAmount(\App\Models\RepairOrder $order): float
    {
        $tasks = $order->tasks->reject(fn ($task) => $task->customer_approval_status === 'rejected');

        $taskTotal = $tasks->sum(function ($task) {
            return (float) ($task->labor_cost ?? 0) + $task->items->sum('subtotal');
        });

        $taskIds = $tasks->pluck('id')->all();
        $standaloneItemsTotal = $order->items
            ->filter(fn ($item) => empty($item->repair_task_id) || ! in_array($item->repair_task_id, $taskIds, true))
            ->sum('subtotal');

        $baseAmount = (float) $taskTotal + (float) $standaloneItemsTotal;

        if ($baseAmount <= 0) {
            return max(0, (float) ($order->subtotal ?: $order->total_amount));
        }

        return $baseAmount;
    }

    public function generateQrCode(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::with(['items', 'tasks.items', 'promotion'])->findOrFail($id);

        if ($order->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Xe chÆ°a hoÃ n thÃ nh, khÃ´ng thá»ƒ táº¡o mÃ£ QR.',
            ], 400);
        }

        $bankId = \App\Models\Setting::get('bank_id', 'vietinbank'); 
        $accountNo = \App\Models\Setting::get('bank_account_no', '102875143924');
        $accountName = urlencode(\App\Models\Setting::get('bank_account_name', 'NGO VAN DAN'));
        $qrTemplate = \App\Models\Setting::get('vietqr_template', 'compact2');
        
        $couponCode = strtoupper(trim((string) $request->query('coupon_code', '')));
        $amounts = $this->paymentAmounts($order, $couponCode);
        if (! $amounts['success']) {
            return response()->json([
                'success' => false,
                'message' => $amounts['message'],
            ], 422);
        }

        $amount = round($amounts['total_amount']); // Ensure integer
        $contentTemplate = \App\Models\Setting::get('qr_payment_content', 'Thanh toan hoa don {order_id}');
        $addInfo = urlencode(str_replace(
            ['{order_id}', '{track_id}'],
            [$order->id, $order->track_id ?? $order->id],
            $contentTemplate
        ));

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$qrTemplate}.png?amount={$amount}&addInfo={$addInfo}&accountName={$accountName}";

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'amount' => $amount,
            'discount_amount' => $amounts['discount_amount'],
        ]);
    }

    public function printInvoice($id)
    {
        $order = \App\Models\RepairOrder::with(['customer', 'vehicle.user', 'advisor', 'promotion', 'tasks.items', 'items'])->findOrFail($id);

        if ((float) ($order->subtotal ?? 0) <= 0) {
            $order->forceFill([
                'subtotal' => $this->paymentBaseAmount($order),
            ])->save();
        }

        $bankId = \App\Models\Setting::get('bank_id', 'vietinbank'); 
        $accountNo = \App\Models\Setting::get('bank_account_no', '102875143924');
        $accountName = urlencode(\App\Models\Setting::get('bank_account_name', 'NGO VAN DAN'));
        $qrTemplate = \App\Models\Setting::get('vietqr_template', 'compact2');
        
        $amount = round($order->total_amount); 
        $contentTemplate = \App\Models\Setting::get('qr_payment_content', 'Thanh toan hoa don {order_id}');
        $addInfo = urlencode(str_replace(
            ['{order_id}', '{track_id}'],
            [$order->id, $order->track_id ?? $order->id],
            $contentTemplate
        ));

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$qrTemplate}.png?amount={$amount}&addInfo={$addInfo}&accountName={$accountName}";

        // Use DomPDF to render
        $pdf = Pdf::loadView('staff.invoices.template', compact('order', 'qrUrl'));
        
        return $pdf->stream('hoadon_' . $order->track_id . '.pdf');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'ThÃ´ng tin há»“ sÆ¡ Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Máº­t kháº©u Ä‘Ã£ Ä‘Æ°á»£c thay Ä‘á»•i.');
    }

    public function schedule(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            $user = User::where('role', 'staff')->first();
        }

        $viewMode = $request->get('view', 'week');
        $dateInput = $request->get('date', now()->toDateString());
        $baseDate = \Carbon\Carbon::parse($dateInput);

        if ($viewMode === 'month') {
            $start = $baseDate->copy()->startOfMonth();
            $end = $baseDate->copy()->endOfMonth();
            $gridStart = $start->copy()->startOfWeek();
            $gridEnd = $end->copy()->endOfWeek();
        } else {
            $start = $baseDate->copy()->startOfWeek();
            $end = $baseDate->copy()->endOfWeek();
            $gridStart = $start;
            $gridEnd = $end;
        }

        $shifts = WorkShift::where('user_id', $user->id)
            ->whereBetween('date', [$gridStart, $gridEnd])
            ->get()
            ->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->toDateString();
            });

        $calendar = [];
        $current = $gridStart->copy();
        
        while ($current <= $gridEnd) {
            $dateStr = $current->toDateString();
            $calendar[] = [
                'date' => $current->copy(),
                'is_current_month' => $viewMode === 'month' ? $current->isSameMonth($baseDate) : true,
                'shift' => $shifts->get($dateStr)
            ];
            $current->addDay();
        }

        return view('staff.schedule.index', compact('calendar', 'viewMode', 'baseDate'));
    }

    // Module 1: Quotations & Parts
    public function searchParts(Request $request)
    {
        $query = $request->get('q');
        $parts = \App\Models\Part::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($part) {
                return [
                    'id' => $part->id,
                    'sku' => $part->sku,
                    'name' => $part->name,
                    'category' => $part->category,
                    'stock_quantity' => $part->stock_quantity,
                    'min_stock' => $part->min_stock,
                    'purchase_price' => (float) ($part->purchase_price ?? 0),
                    'selling_price' => (float) ($part->selling_price ?? 0),
                    'price' => (float) ($part->selling_price ?? 0),
                ];
            });
            
        return response()->json($parts);
    }

    public function storeItem(Request $request, $orderId)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return response()->json(['success' => false, 'message' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n thÃªm váº­t tÆ° vÃ o bÃ¡o giÃ¡.'], 403);
        }

        $order = RepairOrder::findOrFail($orderId);
        if ($order->isLockedForStaffChanges() || in_array($order->status, ['pending_approval', 'approved'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'KhÃ´ng thá»ƒ thÃªm váº­t tÆ° sau khi bÃ¡o giÃ¡ Ä‘Ã£ gá»­i hoáº·c khÃ¡ch Ä‘Ã£ duyá»‡t.'
            ], 409);
        }

        if ($request->input('is_custom') == 'true') {
            // External / Custom Item -> Require Approval
            $request->validate([
                'name' => 'required|string',
                'qty' => 'required|numeric|min:1',
                'cost_price' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0', // Selling Price
            ]);

            \App\Models\MaterialRequest::create([
                'staff_id' => Auth::id(),
                'repair_order_id' => $orderId,
                'part_name' => $request->name,
                'quantity' => $request->qty,
                'cost_price' => $request->cost_price,
                'unit_price' => $request->price,
                'reason' => 'Váº­t tÆ° mua ngoÃ i',
                'status' => 'pending'
            ]);
            $this->logOrderActivity($order, 'STAFF_MATERIAL_REQUESTED', "YÃªu cáº§u váº­t tÆ° mua ngoÃ i {$request->name} x {$request->qty}.");

            return response()->json([
                'success' => true,
                'pending_approval' => true,
                'message' => 'ÄÃ£ gá»­i yÃªu cáº§u váº­t tÆ° Ä‘ang chá» duyá»‡t!'
            ]);

        } else {
            // Inventory Item
            $request->validate([
                'sku' => 'required|exists:parts,sku',
                'qty' => 'required|numeric|min:1',
            ]);

            $part = \App\Models\Part::where('sku', $request->sku)->firstOrFail();
            $unitPrice = $part->selling_price ?? $part->price ?? 0;

            $item = $order->items()->create([
                'name' => $part->name, // Redundant but good for history
                'quantity' => $request->qty,
                'unit_price' => $unitPrice, // Use System Price (or allow override?)
                'cost_price' => $part->purchase_price ?? $part->cost_price ?? 0,
                'subtotal' => $request->qty * $unitPrice,
                'itemable_type' => \App\Models\Part::class,
                'itemable_id' => $part->id,
            ]);
            $this->logOrderActivity($order, 'STAFF_ITEM_ADDED', "ThÃªm váº­t tÆ° {$item->name} x {$item->quantity}.");

            // Deduct Stock
            // $part->decrement('quantity', $request->qty);
        }

        // Update Order Total
        $order->total_amount = $order->items()->sum('subtotal');
        $order->save();

        return response()->json(['success' => true]);
    }
    // Module 2: Internal Chat
    public function getComments($orderId)
    {
        $comments = \App\Models\Comment::with(['user', 'parent.user'])
            ->where('repair_order_id', $orderId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json($comments);
    }

    // Task Management API
    public function showTask($id)
    {
        $task = \App\Models\RepairTask::with(['children.mechanic', 'mechanic', 'repairOrder'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    public function updateTaskDetails(Request $request, $id)
    {
        $task = \App\Models\RepairTask::with('repairOrder')->findOrFail($id);
        if ($task->repairOrder->isLockedForStaffChanges()) {
            return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ cáº­p nháº­t cÃ´ng viá»‡c.'], 409);
        }
        
        // Authorization check (optional: only assigned mechanic or admin?)
        // For now, allow staff to edit.

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'nullable|string'
        ]);

        $task->update($validated);
        $this->logOrderActivity($task->repairOrder, 'STAFF_TASK_UPDATED', "Cáº­p nháº­t chi tiáº¿t task {$task->title}.");

        return response()->json(['success' => true]);
    }

    public function deleteTask($id)
    {
        $task = \App\Models\RepairTask::with('repairOrder')->findOrFail($id);
        if ($task->repairOrder->isLockedForStaffChanges()) {
            return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ xÃ³a cÃ´ng viá»‡c.'], 409);
        }
        
        // Prevent deleting if completed or has specific logic?
        if ($task->status == 'completed') {
             return response()->json(['success' => false, 'message' => 'KhÃ´ng thá»ƒ xÃ³a nhiá»‡m vá»¥ Ä‘Ã£ hoÃ n thÃ nh!'], 400);
        }

        // Explicitly delete associated parts to prevent orphans (due to nullOnDelete constraint)
        \App\Models\RepairOrderItem::where('repair_task_id', $task->id)->delete();

        // Also delete parts for children tasks before deleting the children
        foreach ($task->children as $child) {
            \App\Models\RepairOrderItem::where('repair_task_id', $child->id)->delete();
        }
        $task->children()->delete();

        $title = $task->title;
        $order = $task->repairOrder;
        $task->delete();
        $this->logOrderActivity($order, 'STAFF_TASK_DELETED', "XÃ³a task {$title}.");

        return response()->json(['success' => true]);
    }
    public function storeComment(Request $request, $orderId)
    {
        $request->validate([
            'content' => 'nullable|required_without:attachment',
            'attachment' => 'nullable|image|max:5120' // 5MB
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('comments', 'public');
        }
        
        $comment = \App\Models\Comment::create([
            'repair_order_id' => $orderId,
            'user_id' => Auth::id(),
            'content' => $request->content ?? '',
            'is_internal' => true,
            'parent_id' => $request->parent_id, // For replies
            'attachment_path' => $path
        ]);
        
        return response()->json(['success' => true, 'comment' => $comment->load(['user', 'parent.user'])]);
    }

    // Module 3: Notifications
    public function getNotifications()
    {
        if (\App\Models\Setting::get('enable_notifications', '1') == '0') {
            return redirect()->route('staff.dashboard')->with('info', 'ThÃ´ng bÃ¡o hiá»‡n Ä‘ang bá»‹ táº¯t bá»Ÿi quáº£n trá»‹ viÃªn.');
        }

        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view('staff.notifications.index', compact('notifications'));
    }

    public function markAllNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'ÄÃ£ Ä‘Ã¡nh dáº¥u táº¥t cáº£ lÃ  Ä‘Ã£ Ä‘á»c.');
    }

    public function markNotificationAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    public function sendQuote($id)
    {
        $order = \App\Models\RepairOrder::findOrFail($id);
        $order->update(['quote_status' => 'sent']);
        
        // Notify Customer
        if ($order->customer_id) {
            $customer = \App\Models\User::find($order->customer_id);
            if ($customer) {
                \App\Services\NotificationService::send(
                    $customer,
                    'quote_sent',
                    'BÃ¡o giÃ¡ má»›i',
                    "Gara Ä‘Ã£ gá»­i bÃ¡o giÃ¡ cho Ä‘Æ¡n sá»­a chá»¯a #{$order->id} cá»§a báº¡n.",
                    route('customer.dashboard'),
                    'fas fa-file-invoice-dollar text-amber-500'
                );
            }
        }
        
        return response()->json(['success' => true]);
    }
    public function toggleTask($id)
    {
        $task = \App\Models\RepairTask::with(['children', 'repairOrder'])->find($id); // Eager load children
        if ($task) {
            if ($task->repairOrder->isLockedForStaffChanges()) {
                return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ cáº­p nháº­t cÃ´ng viá»‡c.'], 409);
            }
            if ($task->customer_approval_status === 'rejected') {
                return response()->json(['success' => false, 'message' => 'KhÃ¡ch hÃ ng Ä‘Ã£ tá»« chá»‘i cÃ´ng viá»‡c nÃ y.'], 409);
            }

            // Rule: Cannot complete Parent if Children are pending
            if ($task->status != 'completed') { // Attempting to Complete
                $hasIncompleteApprovedChildren = $task->children
                    ->where('status', '!=', 'completed')
                    ->reject(fn ($child) => $child->customer_approval_status === 'rejected')
                    ->count() > 0;

                if ($hasIncompleteApprovedChildren) {
                     return response()->json([
                         'success' => false, 
                         'message' => 'Vui lÃ²ng hoÃ n thÃ nh táº¥t cáº£ nhiá»‡m vá»¥ con trÆ°á»›c khi hoÃ n thÃ nh nhiá»‡m vá»¥ chÃ­nh nÃ y!'
                     ]);
                }
            }

            $task->status = $task->status == 'completed' ? 'pending' : 'completed';
            $task->save();
            
            // Touch order to update timestamp
            $task->repairOrder->touch();
            $this->logOrderActivity($task->repairOrder, 'STAFF_TASK_TOGGLED', "Chuyá»ƒn task {$task->title} sang {$task->status}.");
            return response()->json(['success' => true, 'status' => $task->status]);
        }
        return response()->json(['success' => false], 404);
    }

    // Generic Note
    public function addNote(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if ($order) {
            if ($order->isLockedForStaffChanges()) {
                return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ thÃªm ghi chÃº.'], 409);
            }

            $note = $request->input('note');
            $timestamp = now()->format('H:i d/m');
            // Append format: "Content [Time]"
            $newEntry = "$note (Ghi bá»Ÿi: TÆ° váº¥n viÃªn â€¢ $timestamp)";
            
            if ($order->notes) {
                // If notes already exists, append new line
                $order->notes .= "\n" . $newEntry;
            } else {
                $order->notes = $newEntry;
            }
            
            $order->save();
            $this->logOrderActivity($order, 'STAFF_NOTE_ADDED', 'ThÃªm ghi chÃº ná»™i bá»™.');
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // Add Material (Quick Ad-hoc)
    public function storeQuickItem(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if ($order) {
            if ($order->isLockedForStaffChanges() || in_array($order->status, ['pending_approval', 'approved'], true)) {
                return response()->json(['success' => false, 'message' => 'KhÃ´ng thá»ƒ thÃªm váº­t tÆ° nhanh sau khi bÃ¡o giÃ¡ Ä‘Ã£ gá»­i hoáº·c khÃ¡ch Ä‘Ã£ duyá»‡t.'], 409);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
            ]);
            $name = $validated['name'];
            $qty = $validated['quantity'];
            
            $order->items()->create([
                'name' => $name,
                'quantity' => $qty,
                'unit_price' => 0, 
                'subtotal' => 0
            ]);
            
            $order->touch();
            $this->logOrderActivity($order, 'STAFF_QUICK_ITEM_ADDED', "ThÃªm váº­t tÆ° nhanh {$name} x {$qty}.");
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // Support Request
    public function requestSupport(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if ($order) {
            if ($order->isLockedForStaffChanges()) {
                return response()->json(['success' => false, 'message' => 'ÄÆ¡n Ä‘Ã£ khÃ³a, khÃ´ng thá»ƒ yÃªu cáº§u há»— trá»£.'], 409);
            }

            $content = $request->input('content') ?? 'KhÃ´ng rÃµ lÃ½ do';
            
            $order->tasks()->create([
                'title' => "YÃªu cáº§u há»— trá»£: $content",
                'type' => 'support',
                'status' => 'pending'
            ]);
            
            $order->touch();
            $this->logOrderActivity($order, 'STAFF_SUPPORT_REQUESTED', "YÃªu cáº§u há»— trá»£: {$content}.");

            \App\Services\NotificationService::notifyAllStaff(
                'support_requested',
                'YÃªu cáº§u há»— trá»£',
                "Ká»¹ thuáº­t viÃªn Ä‘ang chá» há»— trá»£ cho Ä‘Æ¡n '#{$order->id}'.",
                route('staff.order.show', $order->id),
                'fas fa-life-ring'
            );

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // Task Assignment
    public function assignTask(Request $request, $id)
    {
        $task = \App\Models\RepairTask::find($id);
        if ($task) {
            if ($task->mechanic_id && $task->mechanic_id != Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Nhiá»‡m vá»¥ nÃ y Ä‘Ã£ Ä‘Æ°á»£c nháº­n bá»Ÿi ngÆ°á»i khÃ¡c!']);
            }
            
            $task->mechanic_id = Auth::id();
            $task->save();
            
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function unassignTask(Request $request, $id)
    {
        $task = \App\Models\RepairTask::find($id);
        if ($task) {
            if ($task->mechanic_id != Auth::id()) {
                 return response()->json(['success' => false, 'message' => 'Báº¡n khÃ´ng pháº£i ngÆ°á»i nháº­n nhiá»‡m vá»¥ nÃ y!']);
            }
            
            $task->mechanic_id = null;
            $task->save();
            
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function deleteOrder($id)
    {
        if (! $this->canManageStaffOrderFlow()) {
            return response()->json(['success' => false, 'message' => 'Ká»¹ thuáº­t viÃªn khÃ´ng cÃ³ quyá»n xÃ³a Ä‘Æ¡n.'], 403);
        }

        $order = \App\Models\RepairOrder::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'KhÃ´ng tÃ¬m tháº¥y Ä‘Æ¡n sá»­a chá»¯a.'], 404);
        }

        // Protect from deleting orders that have started work
        if ($order->status !== 'pending' && $order->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Xe nÃ y Ä‘Ã£ Ä‘Æ°á»£c tiáº¿p nháº­n vÃ  xá»­ lÃ½, khÃ´ng thá»ƒ xÃ³a trá»±c tiáº¿p!'
            ], 403);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                // Delete tasks
                $order->tasks()->each(function ($task) {
                    $task->children()->delete();
                    $task->delete();
                });
                // Delete parts/items
                $order->items()->delete();
                // Delete VHC report
                if ($order->vhcReport) {
                    $order->vhcReport->defects()->delete();
                    $order->vhcReport->delete();
                }

                // Finally delete order
                $order->delete();
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Lá»—i khi xÃ³a Ä‘Æ¡n sá»­a chá»¯a: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lá»—i há»‡ thá»‘ng khi xÃ³a Ä‘Æ¡n.'
            ], 500);
        }
    }

    private function logOrderActivity(RepairOrder $order, string $action, string $details): void
    {
        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => "Order #{$order->id}: {$details}",
            'ip_address' => request()->ip(),
        ]);
    }

    private function friendlyOrderStatus(?string $status): string
    {
        return match ($status) {
            RepairOrder::STATUS_PENDING => 'Ä‘ang chá» tiáº¿p nháº­n',
            RepairOrder::STATUS_IN_PROGRESS => 'Ä‘ang kiá»ƒm tra/láº­p bÃ¡o giÃ¡',
            RepairOrder::STATUS_PENDING_APPROVAL => 'Ä‘ang chá» khÃ¡ch duyá»‡t',
            RepairOrder::STATUS_APPROVED => 'khÃ¡ch Ä‘Ã£ duyá»‡t',
            RepairOrder::STATUS_COMPLETED => 'Ä‘Ã£ hoÃ n thÃ nh',
            RepairOrder::STATUS_CANCELLED => 'Ä‘Ã£ há»§y',
            default => $status ?: 'khÃ´ng rÃµ',
        };
    }

    private function canManageStaffOrderFlow(): bool
    {
        $user = Auth::user();

        return ! $user || ! $user->isTechnician() || $user->isAdmin() || $user->isManager();
    }

    private function orderActivities(RepairOrder $order)
    {
        return \App\Models\ActivityLog::with('user')
            ->where(function ($query) use ($order) {
                $query->where('details', 'like', "%Order #{$order->id}:%")
                    ->orWhere('details', 'like', "%order #{$order->id}:%")
                    ->orWhere('details', 'like', "%don #{$order->id}%")
                    ->orWhere('details', 'like', "%#{$order->id}%");
            })
            ->latest()
            ->limit(12)
            ->get();
    }

}

