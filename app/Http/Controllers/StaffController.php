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

class StaffController extends Controller
{
    public function __construct(
        protected \App\Services\Staff\VehicleService $vehicleService
    ) {}

    public function dashboard()
    {
        // Kanban Board Data: Fetch Orders instead of Tasks
        // We need 'waiting' (pending), 'inProgress', 'ready' (completed)
        
        $orders = \App\Models\RepairOrder::with(['vehicle', 'vehicle.user', 'advisor', 'items']) // Changed tasks to items if tasks model is not linked effectively or use available relation
            ->withCount('items') // assuming items are tasks? No, 'items' are parts/services.
            // Let's check if 'tasks' relationship exists in RepairOrder. It wasn't in the file I read.
            // Wait, RepairTask has 'repairOrder' belongTo, but RepairOrder didn't show 'tasks' hasMany.
            // I need to add 'tasks' relationship to RepairOrder first if it's missing.
            // For now, I will fetch tasks separately or add the relation. 
            // Actually, let's look at lines 34: $allTasks = ... whereIn repair_order_id.
            // So I can map them manually.
            ->latest()
            ->get();

        // Fetch tasks separately to calculate progress manually
        $allTasks = \App\Models\RepairTask::with(['children', 'mechanic'])->whereIn('repair_order_id', $orders->pluck('id'))->get();

        // Manual mapping for progress since relationship might be missing in model file read earlier
        $orders->transform(function ($order) use ($allTasks) {
            $orderTasks = $allTasks->where('repair_order_id', $order->id);
            $total = $orderTasks->count();
            $completed = $orderTasks->where('status', 'completed')->count();
            
            $order->progress_percent = $total > 0 ? round(($completed / $total) * 100) : 5; // Default 5% for "Started"
            
            // Collect unique mechanic names
            $mechanicNames = $orderTasks->pluck('mechanic.name')->filter()->unique()->values()->all();
            $order->mechanics_display = !empty($mechanicNames) ? implode(', ', $mechanicNames) : 'Chưa phân công';

            // Status Label
            if ($order->status == 'pending') $order->status_label = 'Chờ xử lý';
            elseif (in_array($order->status, ['in_progress', 'pending_approval', 'approved'])) $order->status_label = 'Đang xử lý';
            elseif ($order->status == 'completed') $order->status_label = 'Sẵn sàng';
            else $order->status_label = $order->status === 'cancelled' ? 'Đã hủy' : ucfirst($order->status);

            return $order;
        });

        $waiting = $orders->where('status', 'pending');
        $inProgress = $orders->whereIn('status', ['in_progress', 'pending_approval', 'approved']);
        $ready = $orders->where('status', 'completed');
        
        $allVehicles = \App\Models\Vehicle::all();
        
        // Determine Selected Order
        $selectedId = request('order_id');
        $selectedOrder = null;
        if ($selectedId) {
            $selectedOrder = $orders->firstWhere('id', $selectedId);
        }
        if (!$selectedOrder) {
            $selectedOrder = $inProgress->first() ?? $waiting->first() ?? $ready->first();
        }
        
        $pendingTasks = $allTasks->where('status', 'pending');
        $progressTasks = $allTasks->whereIn('status', ['in_progress', 'completed']);

        if (request()->ajax()) {
            $currentTasks = $allTasks->where('repair_order_id', $selectedOrder->id);
            return view('staff.partials.order_details', compact('selectedOrder', 'currentTasks'));
        }

        return view('staff.dashboard', compact('waiting', 'inProgress', 'ready', 'allVehicles', 'allTasks', 'pendingTasks', 'progressTasks', 'selectedOrder'));
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
            $title = !empty($inspectionOptions['use_3d']) ? 'Kiểm tra tổng quát (3D)' : 'Kiểm tra tổng quát';
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
                'title' => 'Kiểm tra bên trong khoang lái',
                'type' => 'general',
                'status' => 'pending',
            ]);
        }

        if (!empty($inspectionOptions['engine'])) {
            \App\Models\RepairTask::create([
                'repair_order_id' => $order->id,
                'title' => 'Kiểm tra động cơ',
                'type' => 'general',
                'status' => 'pending',
            ]);
        }

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

        $report = \App\Models\VhcReport::firstOrCreate(['repair_order_id' => $order->id]);
        
        // Update Status
        $status = $request->input('status', 'draft');
        $report->status = $status;
        $report->save();

        // Simple Sync Strategy: Delete all and re-create (MVP)
        $report->defects()->delete();

        foreach ($request->input('defects', []) as $d) {
            $report->defects()->create([
                'title' => $d['part'] ?? 'Unknown',
                'description' => $d['description'] ?? '',
                'type' => 'general',
                'severity' => $d['severity'] ?? 'medium',
                'pos_x' => $d['pos']['x'],
                'pos_y' => $d['pos']['y'],
                'pos_z' => $d['pos']['z'],
                'images' => [] 
            ]);
        }

        // 1. Find or Create UNIQUE Parent VHC Task (Reuse existing if available)
        // Improved Logic: Search by Title OR Type to catch existing tasks created without type
        // ALSO: Check for "Kiểm tra tổng quát (VHC)" which seems to be an alternative title in the system
        $parentTask = \App\Models\RepairTask::where('repair_order_id', $order->id)
            ->where(function ($query) {
                $query->where('type', 'vhc')
                      ->orWhere('title', 'Kiểm tra tổng quát (3D)')
                      ->orWhere('title', 'Kiểm tra tổng quát (VHC)');
            })
            ->first();

        if (!$parentTask) {
            $parentTask = \App\Models\RepairTask::create([
                'repair_order_id' => $order->id,
                'type' => 'vhc',
                'title' => 'Kiểm tra tổng quát (3D)', // Standardized Title
                'status' => 'pending'
            ]);
        }
        
        // Ensure type IS set correctly if found by title (self-healing for legacy)
        if ($parentTask->type !== 'vhc') {
            $parentTask->update(['type' => 'vhc']);
        }

        // 2. Auto-generate Repair Task from Defect (Child)
        foreach ($request->input('defects', []) as $d) {
            \App\Models\RepairTask::firstOrCreate(
                [
                    'repair_order_id' => $order->id,
                    'title' => ($d['part'] ?? 'Unknown'),
                    'parent_id' => $parentTask->id
                ],
                [
                    'status' => 'pending',
                    'type' => 'defect'
                ]
            );
        }

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
        $order = \App\Models\RepairOrder::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        $order->update([
            'status' => $validated['status'],
            'estimated_end_time' => $request->input('estimated_end_time') // Optional update
        ]);

        // Auto-assign unassigned tasks to the current staff when starting repair
        if ($validated['status'] === 'in_progress') {
            \App\Models\RepairTask::where('repair_order_id', $order->id)
                ->whereNull('mechanic_id')
                ->update(['mechanic_id' => auth()->id()]);

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

        // Un-assign tasks if reverted back to pending
        if ($validated['status'] === 'pending') {
            \App\Models\RepairTask::where('repair_order_id', $order->id)
                ->update(['mechanic_id' => null]);
        }

        // Notify Customer about status change
        if ($order->customer_id && in_array($validated['status'], ['in_progress', 'completed'])) {
            $customer = \App\Models\User::find($order->customer_id);
            if ($customer) {
                $statusText = $validated['status'] === 'completed' ? 'đã hoàn thành' : 'đang được tiến hành';
                $iconColor = $validated['status'] === 'completed' ? 'text-green-500' : 'text-blue-500';
                $icon = $validated['status'] === 'completed' ? 'fas fa-check-circle' : 'fas fa-tools';
                
                \App\Services\NotificationService::send(
                    $customer,
                    'order_status_updated',
                    'Cập nhật trạng thái sửa chữa',
                    "Đơn sửa chữa #{$order->id} của bạn {$statusText}.",
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:repair_tasks,id',
            'type' => 'required|string'
        ]);

        \App\Models\RepairTask::create([
            'repair_order_id' => $orderId,
            'title' => $validated['title'],
            'parent_id' => $validated['parent_id'],
            'type' => $validated['type'],
            'status' => 'pending'
        ]);

        return response()->json(['success' => true]);
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = \App\Models\RepairTask::with('children')->findOrFail($id);
        $status = $request->input('status');

        // Constraint: Parent cannot be completed if children are not done
        if ($status === 'completed' && $task->children()->where('status', '!=', 'completed')->exists()) {
             return response()->json([
                 'success' => false, 
                 'message' => 'Vui lòng hoàn thành tất cả nhiệm vụ con trước!'
             ], 400);
        }

        $task->update([
            'status' => $status
        ]);
        
        // Touch the order to update timestamp
        $task->repairOrder->touch();
        
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
        }])->findOrFail($id);
        
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
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // Default password
            'role' => 'customer',
            'role_id' => \App\Models\Role::where('slug', 'customer')->value('id')
        ]);

        return redirect()->route('staff.customers.show', $user->id)->with('success', 'Khách hàng mới đã được tạo thành công!');
    }

    public function edit($id)
    {
        $customer = User::findOrFail($id);
        return view('staff.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'phone' => 'required|string|unique:users,phone,'.$id,
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('staff.customers.show', $id)->with('success', 'Thông tin khách hàng đã được cập nhật.');
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
                         ->with('success', 'Thông tin xe đã được cập nhật.');
    }

    public function destroyVehicle($id)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $customerId = $vehicle->user_id; // Save needed ID before delete
        
        // Log Activity
        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_VEHICLE',
            'details' => "Deleted vehicle {$vehicle->license_plate} ({$vehicle->model}) of customer ID: {$customerId}",
            'ip_address' => request()->ip()
        ]);

        $this->vehicleService->delete($vehicle);

        return redirect()->route('staff.customers.show', $customerId)
                         ->with('success', 'Xe đã được xóa thành công khỏi hệ thống.');
    }

    public function profile()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function processPayment(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::findOrFail($id);

        if ($order->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Xe chưa hoàn thành, không thể thanh toán.',
            ], 400);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn sửa chữa này đã được thanh toán.',
            ], 400);
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $request->input('payment_method', 'cash'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công',
        ]);
    }

    public function generateQrCode($id)
    {
        $order = \App\Models\RepairOrder::findOrFail($id);

        if ($order->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Xe chưa hoàn thành, không thể tạo mã QR.',
            ], 400);
        }

        $bankId = \App\Models\Setting::get('bank_id', 'vietinbank'); 
        $accountNo = \App\Models\Setting::get('bank_account_no', '102875143924');
        $accountName = urlencode(\App\Models\Setting::get('bank_account_name', 'NGO VAN DAN'));
        
        $amount = round($order->total_amount); // Ensure integer
        $addInfo = urlencode('Thanh toan hoa don ' . $order->id);

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$amount}&addInfo={$addInfo}&accountName={$accountName}";

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'amount' => $amount,
        ]);
    }

    public function printInvoice($id)
    {
        $order = \App\Models\RepairOrder::with(['vehicle.user', 'advisor', 'tasks.children', 'tasks.items'])->findOrFail($id);

        $bankId = \App\Models\Setting::get('bank_id', 'vietinbank'); 
        $accountNo = \App\Models\Setting::get('bank_account_no', '102875143924');
        $accountName = urlencode(\App\Models\Setting::get('bank_account_name', 'NGO VAN DAN'));
        
        $amount = round($order->total_amount); 
        $addInfo = urlencode('Thanh toan hoa don ' . $order->id);

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$amount}&addInfo={$addInfo}&accountName={$accountName}";

        return view('staff.order.invoice', compact('order', 'qrUrl'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Thông tin hồ sơ đã được cập nhật.');
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

        return back()->with('success', 'Mật khẩu đã được thay đổi.');
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
            ->get();
            
        return response()->json($parts);
    }

    public function storeItem(Request $request, $orderId)
    {
        $order = RepairOrder::findOrFail($orderId);

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
                'reason' => 'Vật tư mua ngoài',
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'pending_approval' => true,
                'message' => 'Đã gửi yêu cầu vật tư đang chờ duyệt!'
            ]);

        } else {
            // Inventory Item
            $request->validate([
                'sku' => 'required|exists:parts,sku',
                'qty' => 'required|numeric|min:1',
            ]);

            $part = \App\Models\Part::where('sku', $request->sku)->firstOrFail();

            $item = $order->items()->create([
                'name' => $part->name, // Redundant but good for history
                'quantity' => $request->qty,
                'unit_price' => $part->price, // Use System Price (or allow override?)
                'cost_price' => $part->cost_price ?? 0, // Assuming Part has cost_price
                'subtotal' => $request->qty * $part->price,
                'itemable_type' => \App\Models\Part::class,
                'itemable_id' => $part->id,
            ]);

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
        $task = \App\Models\RepairTask::findOrFail($id);
        
        // Authorization check (optional: only assigned mechanic or admin?)
        // For now, allow staff to edit.

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'nullable|string'
        ]);

        $task->update($validated);

        return response()->json(['success' => true]);
    }

    public function deleteTask($id)
    {
        $task = \App\Models\RepairTask::findOrFail($id);
        
        // Prevent deleting if completed or has specific logic?
        if ($task->status == 'completed') {
             return response()->json(['success' => false, 'message' => 'Không thể xóa nhiệm vụ đã hoàn thành!'], 400);
        }

        // Explicitly delete associated parts to prevent orphans (due to nullOnDelete constraint)
        \App\Models\RepairOrderItem::where('repair_task_id', $task->id)->delete();

        // Also delete parts for children tasks before deleting the children
        foreach ($task->children as $child) {
            \App\Models\RepairOrderItem::where('repair_task_id', $child->id)->delete();
        }
        $task->children()->delete();

        $task->delete();

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
        $notifications = Auth::user()->notifications()->latest()->paginate(20);
        return view('staff.notifications.index', compact('notifications'));
    }

    public function markAllNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
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
                    'Báo giá mới',
                    "Gara đã gửi báo giá cho đơn sửa chữa #{$order->id} của bạn.",
                    route('customer.dashboard'),
                    'fas fa-file-invoice-dollar text-amber-500'
                );
            }
        }
        
        return response()->json(['success' => true]);
    }
    public function toggleTask($id)
    {
        $task = \App\Models\RepairTask::with('children')->find($id); // Eager load children
        if ($task) {
            // Rule: Cannot complete Parent if Children are pending
            if ($task->status != 'completed') { // Attempting to Complete
                if ($task->children->where('status', '!=', 'completed')->count() > 0) {
                     return response()->json([
                         'success' => false, 
                         'message' => 'Vui lòng hoàn thành tất cả nhiệm vụ con trước khi hoàn thành nhiệm vụ chính này!'
                     ]);
                }
            }

            $task->status = $task->status == 'completed' ? 'pending' : 'completed';
            $task->save();
            
            // Touch order to update timestamp
            $task->repairOrder->touch();
            return response()->json(['success' => true, 'status' => $task->status]);
        }
        return response()->json(['success' => false], 404);
    }

    // Generic Note
    public function addNote(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if ($order) {
            $note = $request->input('note');
            $timestamp = now()->format('H:i d/m');
            // Append format: "Content [Time]"
            $newEntry = "$note (Ghi bởi: Tư vấn viên • $timestamp)";
            
            if ($order->notes) {
                // If notes already exists, append new line
                $order->notes .= "\n" . $newEntry;
            } else {
                $order->notes = $newEntry;
            }
            
            $order->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // Add Material (Quick Ad-hoc)
    public function storeQuickItem(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if ($order) {
            $name = $request->input('name');
            $qty = $request->input('quantity', 1);
            
            $order->items()->create([
                'name' => $name,
                'quantity' => $qty,
                'unit_price' => 0, 
                'subtotal' => 0
            ]);
            
            $order->touch();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // Support Request
    public function requestSupport(Request $request, $id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if ($order) {
            $content = $request->input('content') ?? 'Không rõ lý do';
            
            $order->tasks()->create([
                'title' => "Yêu cầu hỗ trợ: $content",
                'type' => 'support',
                'status' => 'pending'
            ]);
            
            $order->touch();

            \App\Services\NotificationService::notifyAllStaff(
                'support_requested',
                'Yêu cầu hỗ trợ',
                "Kỹ thuật viên đang chờ hỗ trợ cho đơn '#{$order->id}'.",
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
                return response()->json(['success' => false, 'message' => 'Nhiệm vụ này đã được nhận bởi người khác!']);
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
                 return response()->json(['success' => false, 'message' => 'Bạn không phải người nhận nhiệm vụ này!']);
            }
            
            $task->mechanic_id = null;
            $task->save();
            
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function deleteOrder($id)
    {
        $order = \App\Models\RepairOrder::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn sửa chữa.'], 404);
        }

        // Protect from deleting orders that have started work
        if ($order->status !== 'pending' && $order->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Xe này đã được tiếp nhận và xử lý, không thể xóa trực tiếp!'
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
            \Log::error('Lỗi khi xóa đơn sửa chữa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi xóa đơn.'
            ], 500);
        }
    }
}
