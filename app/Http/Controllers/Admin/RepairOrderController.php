<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Part;
use App\Models\RepairOrderItem;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class RepairOrderController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view_repair_orders');
        
        $query = RepairOrder::with(['customer', 'vehicle', 'advisor'])
            ->latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('track_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function($q) use ($search) {
                      $q->where('license_plate', 'like', "%{$search}%");
                  });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $repairOrders = $query->paginate(10);

        $stats = [
            'total' => RepairOrder::count(),
            'pending' => RepairOrder::where('status', 'pending')->count(),
            'in_progress' => RepairOrder::where('status', 'in_progress')->count(),
            'due_today' => RepairOrder::whereDate('expected_completion_date', today())->count(),
        ];

        return view('admin.repair_orders.index', compact('repairOrders', 'stats'));
    }

    public function create()
    {
        Gate::authorize('create_repair_orders');
        $customers = User::where('role', 'customer')->get(); 
        // We will load vehicles dynamically via AJAX based on customer selection
        return view('admin.repair_orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_repair_orders');
        
        $validated = $request->validate([
            // Customer Validation
            'customer_id' => 'nullable|exists:users,id',
            'customer_phone' => 'required_without:customer_id|nullable|string',
            'customer_name' => 'required_without:customer_id|nullable|string',
            'customer_email' => 'nullable|email',

            // Vehicle Validation
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_license_plate' => 'required_without:vehicle_id|nullable|string',
            'vehicle_model' => 'required_without:vehicle_id|nullable|string',
            'vehicle_make' => 'nullable|string',
            'vehicle_type' => 'nullable|string', // Sedan, SUV, etc.
            'vehicle_year' => 'nullable|integer',
            'vehicle_vin' => 'nullable|string',
            
            // Order Details
            'odometer_reading' => 'nullable|integer',
            'expected_completion_date' => 'nullable|date',
            'diagnosis_note' => 'nullable|string',
        ]);

        // 1. Handle Customer
        if ($request->filled('customer_id')) {
            $customer = User::find($request->customer_id);
        } else {
            // Check if customer exists by phone
            $customer = User::where('phone', $request->customer_phone)->first();
            
            if (!$customer) {
                // Create new Customer
                $customer = User::create([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email, // Can be null
                    'password' => \Illuminate\Support\Facades\Hash::make('12345678'), // Default password
                    'role' => 'customer',
                    'role_id' => \App\Models\Role::where('slug', 'customer')->first()->id ?? 1, // Fallback role ID
                ]);
            }
        }

        // 2. Handle Vehicle
        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::find($request->vehicle_id);
        } else {
            // Check if vehicle exists by license plate
            $plate = strtoupper(str_replace(['-', ' '], '', $request->vehicle_license_plate)); // Normalize
            $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(license_plate, '-', ''), ' ', '') = ?", [$plate])->first();

            if (!$vehicle) {
                $vehicle = Vehicle::create([
                    'user_id' => $customer->id,
                    'license_plate' => $request->vehicle_license_plate,
                    'model' => $request->vehicle_model,
                    'make' => $request->vehicle_make ?? 'Unknown',
                    'type' => $request->vehicle_type ?? 'sedan',
                    'year' => $request->vehicle_year ?? date('Y'),
                    'vin' => $request->vehicle_vin,
                    'owner_name' => $customer->name,
                    'owner_phone' => $customer->phone,
                ]);
            } else {
                // If vehicle exists but user_id is null (Walk-in), assign it?
                // Or just use it.
                if (!$vehicle->user_id) {
                    $vehicle->update(['user_id' => $customer->id]);
                }
            }
        }

        $ro = RepairOrder::create([
            'track_id' => 'LSC-' . strtoupper(Str::random(8)),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => auth()->id(),
            'status' => 'pending',
            'odometer_reading' => $validated['odometer_reading'],
            'expected_completion_date' => $validated['expected_completion_date'],
            'diagnosis_note' => $validated['diagnosis_note'],
        ]);

        // Auto-generate Default Tasks
        $defaultTasks = [
            'Tiếp nhận xe & Kiểm tra sơ bộ',
            'Kiểm tra kỹ thuật chi tiết',
            'Báo giá & Chờ khách duyệt',
            'Thực hiện sửa chữa / bảo dưỡng',
            'Kiểm tra chất lượng sau sửa chữa (QC)',
            'Rửa xe & Vệ sinh nội thất',
            'Bàn giao xe & Thanh toán'
        ];

        foreach ($defaultTasks as $index => $title) {
            \App\Models\RepairTask::create([
                'repair_order_id' => $ro->id,
                'title' => $title,
                'status' => 'pending',
                'description' => 'Công việc tiêu chuẩn quy trình',
                // 'assigned_to' => null // Can be assigned later
            ]);
        }

        return redirect()->route('admin.repair_orders.show', $ro)
            ->with('success', 'Lệnh tiếp nhận đã được tạo thành công!');
    }

    public function show(RepairOrder $repairOrder)
    {
        Gate::authorize('view_repair_orders');
        $repairOrder->load(['customer', 'vehicle', 'items.itemable']);
        $services = Service::all();
        $parts = Part::where('stock_quantity', '>', 0)->get();
        return view('admin.repair_orders.show', compact('repairOrder', 'services', 'parts'));
    }

    public function storeItem(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        
        $validated = $request->validate([
            'type' => 'required|in:service,part',
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated['type'] === 'service') {
            $item = Service::findOrFail($validated['item_id']);
            $unitPrice = $item->base_price;
            $modelType = Service::class;
        } else {
            $item = Part::findOrFail($validated['item_id']);
            $unitPrice = $item->selling_price;
            $modelType = Part::class;
            
            // Basic stock check
            if ($item->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'Insufficient stock for this part.');
            }
        }

        $subtotal = $unitPrice * $validated['quantity'];

        $repairOrder->items()->create([
            'itemable_type' => $modelType,
            'itemable_id' => $item->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        $this->recalculateTotal($repairOrder);

        return back()->with('success', 'Item added successfully');
    }

    public function updateStatus(Request $request, RepairOrder $repairOrder)
    {
        Gate::any(['manage_repair_orders', 'approve_repair_orders', 'update_repair_progress']);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,in_progress,completed,cancelled'
        ]);

        $repairOrder->update(['status' => $validated['status']]);

        // If completed, deduct stock (Simplified logic for now)
        // ideally we deduct when allocated or in_progress, but let's stick to completed for this check
        // Or better: Deduct when "Approved"? Let's allow negative stock for simplicity or handle it later.
        // For now: Just status update.

        return back()->with('success', 'Status updated to ' . ucfirst($validated['status']));
    }

    public function destroyItem(RepairOrder $repairOrder, RepairOrderItem $item)
    {
        Gate::authorize('manage_repair_orders');
        $item->delete();
        $this->recalculateTotal($repairOrder);
        return back()->with('success', 'Item removed');
    }

    public function invoice(RepairOrder $repairOrder)
    {
        Gate::authorize('view_repair_orders');
        $repairOrder->load(['customer', 'vehicle', 'items.itemable', 'advisor']);
        return view('admin.repair_orders.invoice', compact('repairOrder'));
    }

    public function updatePayment(Request $request, RepairOrder $repairOrder)
    {
        Gate::any(['manage_finance', 'manage_repair_orders']);
        
        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $repairOrder->update([
            'payment_status' => $validated['payment_status'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes']
        ]);

        // Auto-update order status logic (optional)
        // if ($validated['payment_status'] === 'paid' && $repairOrder->status !== 'completed') {
        //     $repairOrder->update(['status' => 'completed']);
        // }

        return back()->with('success', 'Payment status updated');
    }

    public function applyCoupon(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        
        $request->validate(['code' => 'required|string']);
        
        $code = strtoupper($request->code);
        $promotion = \App\Models\Promotion::where('code', $code)->first();

        // 1. Check existence
        if (!$promotion) {
            return back()->withErrors(['coupon' => 'Mã giảm giá không tồn tại!']);
        }

        // 2. Check validity (Date, Limit, Active)
        if (!$promotion->isValid()) {
             return back()->withErrors(['coupon' => 'Mã giảm giá đã hết hạn hoặc không khả dụng!']);
        }

        // 3. Check specific customer
        if ($promotion->customer_id && $promotion->customer_id !== $repairOrder->customer_id) {
             return back()->withErrors(['coupon' => 'Mã này không áp dụng cho khách hàng này!']);
        }

        // 4. Apply
        $repairOrder->promotion_id = $promotion->id;
        $this->recalculateTotal($repairOrder); // Will calculate discount amount inside
        
        // Increment usage (Optional: only increment when marked as Paid? For now, increment on apply)
        //$promotion->increment('used_count'); 

        return back()->with('success', 'Đã áp dụng mã giảm giá: ' . $promotion->code);
    }

    public function removeCoupon(RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        $repairOrder->promotion_id = null;
        $repairOrder->discount_amount = 0;
        $this->recalculateTotal($repairOrder);
        return back()->with('success', 'Đã gỡ bỏ mã giảm giá');
    }

    private function recalculateTotal(RepairOrder $ro)
    {
        $subtotal = $ro->items()->sum('subtotal');
        $ro->subtotal = $subtotal;
        
        // Calculate Discount
        $discount = 0;
        if ($ro->promotion_id) {
            $promo = $ro->promotion; // Ensure relation is loaded or load it
            if ($promo && $promo->isValid()) {
                if ($promo->type == 'fixed') {
                    $discount = $promo->value;
                } else {
                    $discount = $subtotal * ($promo->value / 100);
                }
            } else {
                // If invalid (expired since applied), remove it
                $ro->promotion_id = null;
            }
        }
        
        // Ensure discount doesn't exceed subtotal
        $discount = min($discount, $subtotal);
        $ro->discount_amount = $discount;
        
        // Calculate Tax (e.g., 10% VAT standard, can be config later)
        // $tax = ($subtotal - $discount) * 0.10; 
        $tax = 0; // Keeping simple for now or use Settings later
        $ro->tax_amount = $tax;

        $ro->total_amount = $subtotal - $discount + $tax;
        $ro->save();
    }

    public function storeTask(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $repairOrder->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'assigned_to' => $validated['assigned_to'],
            'status' => 'pending'
        ]);

        return back()->with('success', 'Đã thêm công việc mới');
    }

    public function updateTaskStatus(Request $request, \App\Models\RepairTask $task)
    {
        Gate::any(['manage_repair_orders', 'update_repair_progress']);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task->update(['status' => $validated['status']]);

        return back()->with('success', 'Cập nhật trạng thái công việc thành công');
    }
}
