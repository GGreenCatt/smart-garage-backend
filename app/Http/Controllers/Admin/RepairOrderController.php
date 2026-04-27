<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Part;
use App\Models\Promotion;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairTask;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RepairOrderController extends Controller
{
    private array $statusLabels = [
        RepairOrder::STATUS_PENDING => 'Chờ tiếp nhận',
        RepairOrder::STATUS_IN_PROGRESS => 'Đang sửa chữa',
        RepairOrder::STATUS_PENDING_APPROVAL => 'Chờ khách duyệt báo giá',
        RepairOrder::STATUS_APPROVED => 'Khách đã duyệt',
        RepairOrder::STATUS_COMPLETED => 'Hoàn thành',
        RepairOrder::STATUS_CANCELLED => 'Đã hủy',
    ];

    public function index(Request $request)
    {
        Gate::authorize('view_repair_orders');

        $query = RepairOrder::with(['customer', 'vehicle', 'advisor', 'tasks'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('track_id', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('license_plate', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('make', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $repairOrders = $query->paginate(10)->withQueryString();
        $stats = [
            'total' => RepairOrder::count(),
            'pending' => RepairOrder::where('status', RepairOrder::STATUS_PENDING)->count(),
            'pending_approval' => RepairOrder::where('status', RepairOrder::STATUS_PENDING_APPROVAL)->count(),
            'in_progress' => RepairOrder::whereIn('status', [RepairOrder::STATUS_IN_PROGRESS, RepairOrder::STATUS_APPROVED])->count(),
            'due_today' => RepairOrder::whereDate('expected_completion_date', today())->count(),
        ];
        $statusLabels = $this->statusLabels;

        return view('admin.repair_orders.index', compact('repairOrders', 'stats', 'statusLabels'));
    }

    public function create()
    {
        Gate::authorize('create_repair_orders');

        $customers = User::where(function ($query) {
            $query->where('role', 'customer')
                ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->where('slug', 'customer'));
        })->orderBy('name')->get(['id', 'name', 'phone', 'email']);

        return view('admin.repair_orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_repair_orders');

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'customer_phone' => 'required_without:customer_id|nullable|string|max:30',
            'customer_name' => 'required_without:customer_id|nullable|string|max:255',
            'customer_email' => 'nullable|email',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_license_plate' => 'required_without:vehicle_id|nullable|string|max:30',
            'vehicle_model' => 'required_without:vehicle_id|nullable|string|max:150',
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_year' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'vehicle_vin' => 'nullable|string|max:50',
            'odometer_reading' => 'nullable|integer|min:0',
            'expected_completion_date' => 'nullable|date',
            'diagnosis_note' => 'nullable|string',
        ]);

        $customer = $this->resolveCustomer($validated);
        $vehicle = $this->resolveVehicle($validated, $customer);

        $repairOrder = RepairOrder::create([
            'track_id' => 'PSC-'.strtoupper(Str::random(8)),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => auth()->id(),
            'status' => RepairOrder::STATUS_PENDING,
            'quote_status' => 'draft',
            'payment_status' => 'unpaid',
            'odometer_reading' => $validated['odometer_reading'] ?? null,
            'expected_completion_date' => $validated['expected_completion_date'] ?? null,
            'diagnosis_note' => $validated['diagnosis_note'] ?? null,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        foreach ($this->defaultTasks() as $title) {
            $repairOrder->tasks()->create([
                'title' => $title,
                'status' => 'pending',
                'customer_approval_status' => 'not_required',
                'type' => 'inspection',
                'description' => 'Công việc tiêu chuẩn trong quy trình tiếp nhận và sửa chữa.',
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_REPAIR_ORDER',
            'details' => "Tạo phiếu sửa chữa {$repairOrder->track_id} cho xe {$vehicle->license_plate}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.repair_orders.show', $repairOrder)
            ->with('success', 'Đã tạo phiếu sửa chữa thành công');
    }

    public function show(RepairOrder $repairOrder)
    {
        Gate::authorize('view_repair_orders');

        $repairOrder->load(['customer', 'vehicle', 'advisor', 'tasks.children', 'items.itemable', 'promotion']);
        $services = Service::orderBy('name')->get();
        $parts = Part::where('stock_quantity', '>', 0)->orderBy('name')->get();
        $statusLabels = $this->statusLabels;

        return view('admin.repair_orders.show', compact('repairOrder', 'services', 'parts', 'statusLabels'));
    }

    public function edit(RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');

        return redirect()->route('admin.repair_orders.show', $repairOrder)
            ->with('info', 'Thông tin phiếu sửa chữa được chỉnh trực tiếp tại màn chi tiết');
    }

    public function update(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        $this->ensureOrderEditable($repairOrder);

        $validated = $request->validate([
            'expected_completion_date' => 'nullable|date',
            'diagnosis_note' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $repairOrder->update($validated);

        return redirect()->route('admin.repair_orders.show', $repairOrder)
            ->with('success', 'Đã cập nhật phiếu sửa chữa');
    }

    public function destroy(RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');

        if ($repairOrder->status !== RepairOrder::STATUS_PENDING || $repairOrder->items()->exists() || $repairOrder->tasks()->where('status', '!=', 'pending')->exists()) {
            return back()->with('error', 'Không thể xóa phiếu đã bắt đầu xử lý. Hãy chuyển sang trạng thái đã hủy nếu cần ngừng xử lý.');
        }

        $trackId = $repairOrder->track_id;
        $repairOrder->tasks()->delete();
        $repairOrder->items()->delete();
        $repairOrder->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE_REPAIR_ORDER',
            'details' => "Xóa phiếu sửa chữa {$trackId}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.repair_orders.index')->with('success', 'Đã xóa phiếu sửa chữa');
    }

    public function storeItem(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        $this->ensureOrderEditable($repairOrder);

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

            if ($item->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'Vật tư không đủ tồn kho');
            }
        }

        $repairOrder->items()->create([
            'itemable_type' => $modelType,
            'itemable_id' => $item->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $validated['quantity'],
        ]);

        $this->recalculateTotal($repairOrder);

        return back()->with('success', 'Đã thêm hạng mục vào phiếu sửa chữa');
    }

    public function updateStatus(Request $request, RepairOrder $repairOrder)
    {
        Gate::any(['manage_repair_orders', 'approve_repair_orders', 'update_repair_progress']);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statusLabels))],
        ]);

        if ($repairOrder->isLockedForStaffChanges() && $validated['status'] !== $repairOrder->status) {
            return back()->with('error', 'Phiếu đã hoàn tất hoặc đã hủy nên không thể đổi trạng thái');
        }

        if ($validated['status'] === RepairOrder::STATUS_COMPLETED) {
            $unfinishedTasks = $repairOrder->tasks()->where('status', '!=', 'completed')->count();
            if ($unfinishedTasks > 0) {
                return back()->with('error', "Còn {$unfinishedTasks} công việc chưa hoàn thành");
            }
        }

        $oldStatus = $repairOrder->status;
        $repairOrder->update(['status' => $validated['status']]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_REPAIR_ORDER_STATUS',
            'details' => "Phiếu {$repairOrder->track_id}: đổi trạng thái từ {$this->statusLabels[$oldStatus]} sang {$this->statusLabels[$validated['status']]}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái phiếu sửa chữa');
    }

    public function destroyItem(RepairOrder $repairOrder, RepairOrderItem $item)
    {
        Gate::authorize('manage_repair_orders');
        $this->ensureOrderEditable($repairOrder);

        abort_if($item->repair_order_id !== $repairOrder->id, 404);
        $item->delete();
        $this->recalculateTotal($repairOrder);

        return back()->with('success', 'Đã xóa hạng mục');
    }

    public function invoice(RepairOrder $repairOrder)
    {
        Gate::authorize('view_repair_orders');
        $repairOrder->load(['customer', 'vehicle', 'tasks', 'items.itemable', 'advisor']);

        $bankId = Setting::get('bank_id', 'vietinbank');
        $accountNo = Setting::get('bank_account_no', '102875143924');
        $accountName = urlencode(Setting::get('bank_account_name', 'NGO VAN DAN'));
        $qrTemplate = Setting::get('vietqr_template', 'compact2');
        $amount = round($repairOrder->total_amount);
        $contentTemplate = Setting::get('qr_payment_content', 'Thanh toan hoa don {order_id}');
        $addInfo = urlencode(str_replace(
            ['{order_id}', '{track_id}'],
            [$repairOrder->id, $repairOrder->track_id ?? $repairOrder->id],
            $contentTemplate
        ));

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$qrTemplate}.png?amount={$amount}&addInfo={$addInfo}&accountName={$accountName}";
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('staff.invoices.template', ['order' => $repairOrder, 'qrUrl' => $qrUrl]);

        return $pdf->stream('hoadon_'.$repairOrder->track_id.'.pdf');
    }

    public function updatePayment(Request $request, RepairOrder $repairOrder)
    {
        Gate::any(['manage_finance', 'manage_repair_orders']);

        if ($repairOrder->status !== RepairOrder::STATUS_COMPLETED) {
            return back()->with('error', 'Chỉ thanh toán khi phiếu đã hoàn thành');
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $repairOrder->update($validated);

        return back()->with('success', 'Đã cập nhật thanh toán');
    }

    public function applyCoupon(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        $this->ensureOrderEditable($repairOrder);

        $request->validate(['code' => 'required|string']);
        $promotion = Promotion::where('code', strtoupper($request->code))->first();

        if (! $promotion) {
            return back()->withErrors(['coupon' => 'Mã giảm giá không tồn tại']);
        }

        if (! $promotion->isValid()) {
            return back()->withErrors(['coupon' => 'Mã giảm giá đã hết hạn hoặc không khả dụng']);
        }

        if ($promotion->customer_id && $promotion->customer_id !== $repairOrder->customer_id) {
            return back()->withErrors(['coupon' => 'Mã này không áp dụng cho khách hàng này']);
        }

        $repairOrder->promotion_id = $promotion->id;
        $this->recalculateTotal($repairOrder);

        return back()->with('success', 'Đã áp dụng mã giảm giá: '.$promotion->code);
    }

    public function removeCoupon(RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        $this->ensureOrderEditable($repairOrder);

        $repairOrder->promotion_id = null;
        $repairOrder->discount_amount = 0;
        $this->recalculateTotal($repairOrder);

        return back()->with('success', 'Đã gỡ mã giảm giá');
    }

    public function storeTask(Request $request, RepairOrder $repairOrder)
    {
        Gate::authorize('manage_repair_orders');
        $this->ensureOrderEditable($repairOrder);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mechanic_id' => 'nullable|exists:users,id',
        ]);

        $repairOrder->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'status' => 'pending',
            'customer_approval_status' => 'not_required',
            'type' => 'repair',
        ]);

        return back()->with('success', 'Đã thêm công việc mới');
    }

    public function updateTaskStatus(Request $request, RepairTask $task)
    {
        Gate::any(['manage_repair_orders', 'update_repair_progress']);
        $task->load('repairOrder', 'children');
        $this->ensureOrderEditable($task->repairOrder);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        if ($validated['status'] === 'completed' && $task->children()->where('status', '!=', 'completed')->exists()) {
            return back()->with('error', 'Không thể hoàn thành công việc cha khi còn công việc con chưa xong');
        }

        $task->update(['status' => $validated['status']]);

        return back()->with('success', 'Đã cập nhật trạng thái công việc');
    }

    private function resolveCustomer(array $validated): User
    {
        if (! empty($validated['customer_id'])) {
            return User::findOrFail($validated['customer_id']);
        }

        $customer = User::where('phone', $validated['customer_phone'])->first();
        if ($customer) {
            return $customer;
        }

        $customerRole = Role::where('slug', 'customer')->first();

        return User::create([
            'name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'],
            'email' => $validated['customer_email'] ?? null,
            'password' => Hash::make('12345678'),
            'role' => 'customer',
            'role_id' => $customerRole?->id,
            'status' => 'active',
        ]);
    }

    private function resolveVehicle(array $validated, User $customer): Vehicle
    {
        if (! empty($validated['vehicle_id'])) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            if (! $vehicle->user_id) {
                $vehicle->update([
                    'user_id' => $customer->id,
                    'owner_name' => $customer->name,
                    'owner_phone' => $customer->phone,
                ]);
            }

            return $vehicle;
        }

        $plate = strtoupper(str_replace(['-', ' ', '.'], '', $validated['vehicle_license_plate']));
        $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(REPLACE(UPPER(license_plate), '-', ''), ' ', ''), '.', '') = ?", [$plate])->first();

        if ($vehicle) {
            if (! $vehicle->user_id) {
                $vehicle->update([
                    'user_id' => $customer->id,
                    'owner_name' => $customer->name,
                    'owner_phone' => $customer->phone,
                ]);
            }

            return $vehicle;
        }

        return Vehicle::create([
            'user_id' => $customer->id,
            'license_plate' => $validated['vehicle_license_plate'],
            'model' => $validated['vehicle_model'],
            'make' => $validated['vehicle_make'] ?? null,
            'type' => $validated['vehicle_type'] ?? 'sedan',
            'year' => $validated['vehicle_year'] ?? date('Y'),
            'vin' => $validated['vehicle_vin'] ?? null,
            'owner_name' => $customer->name,
            'owner_phone' => $customer->phone,
        ]);
    }

    private function defaultTasks(): array
    {
        return [
            'Tiếp nhận xe và kiểm tra sơ bộ',
            'Kiểm tra kỹ thuật chi tiết',
            'Lập báo giá và chờ khách duyệt',
            'Thực hiện sửa chữa / bảo dưỡng',
            'Kiểm tra chất lượng sau sửa chữa',
            'Vệ sinh và bàn giao xe',
            'Thanh toán tại quầy',
        ];
    }

    private function recalculateTotal(RepairOrder $repairOrder): void
    {
        $subtotal = $repairOrder->items()->sum('subtotal');
        $discount = 0;

        if ($repairOrder->promotion_id && $repairOrder->promotion?->isValid()) {
            $discount = $repairOrder->promotion->type === 'fixed'
                ? $repairOrder->promotion->value
                : $subtotal * ($repairOrder->promotion->value / 100);
        } elseif ($repairOrder->promotion_id) {
            $repairOrder->promotion_id = null;
        }

        $discount = min($discount, $subtotal);

        $repairOrder->subtotal = $subtotal;
        $repairOrder->discount_amount = $discount;
        $repairOrder->tax_amount = 0;
        $repairOrder->total_amount = $subtotal - $discount;
        $repairOrder->save();
    }

    private function ensureOrderEditable(RepairOrder $repairOrder): void
    {
        if ($repairOrder->isLockedForStaffChanges()) {
            abort(403, 'Phiếu đã hoàn tất, đã hủy hoặc đã thanh toán nên không thể chỉnh sửa');
        }
    }
}
