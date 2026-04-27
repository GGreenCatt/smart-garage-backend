<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    private array $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'completed' => 'Đã tiếp nhận',
        'cancelled' => 'Đã hủy',
        'no_show' => 'Khách không đến',
    ];

    public function index(Request $request)
    {
        Gate::authorize('manage_appointments');

        $month = $this->resolveMonth($request->input('month'));
        $query = Appointment::with(['customer', 'vehicle', 'service'])
            ->when($request->filled('status') && $request->status !== 'all', fn ($appointmentQuery) => $appointmentQuery->where('status', $request->status))
            ->when($request->filled('date'), fn ($appointmentQuery) => $appointmentQuery->whereDate('scheduled_at', $request->date))
            ->when($request->filled('q'), function ($appointmentQuery) use ($request) {
                $keyword = trim($request->q);

                $appointmentQuery->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->where('license_plate', 'like', "%{$keyword}%")
                        ->orWhere('vehicle_name', 'like', "%{$keyword}%")
                        ->orWhere('reason', 'like', "%{$keyword}%")
                        ->orWhere('notes', 'like', "%{$keyword}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                            $customerQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($keyword) {
                            $vehicleQuery->where('license_plate', 'like', "%{$keyword}%")
                                ->orWhere('model', 'like', "%{$keyword}%")
                                ->orWhere('make', 'like', "%{$keyword}%");
                        });
                });
            });

        $appointments = $query->orderBy('scheduled_at')->get();
        $calendarAppointments = Appointment::with(['customer', 'vehicle', 'service'])
            ->whereBetween('scheduled_at', [$month->copy()->startOfMonth()->startOfWeek(), $month->copy()->endOfMonth()->endOfWeek()])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->scheduled_at->toDateString());

        $calendarDays = collect(CarbonPeriod::create(
            $month->copy()->startOfMonth()->startOfWeek(),
            $month->copy()->endOfMonth()->endOfWeek()
        ));

        $services = Service::query()
            ->when(Schema::hasColumn('services', 'is_active'), fn ($serviceQuery) => $serviceQuery->where('is_active', true))
            ->orderBy('name')
            ->get();

        $customers = User::where(function ($customerQuery) {
            $customerQuery->where('role', 'customer')
                ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->where('slug', 'customer'));
        })->orderBy('name')->get(['id', 'name', 'phone', 'email']);

        $stats = [
            'today' => Appointment::whereDate('scheduled_at', today())->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'upcoming' => Appointment::whereIn('status', ['pending', 'confirmed'])
                ->where('scheduled_at', '>=', now())
                ->count(),
        ];

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'calendarAppointments' => $calendarAppointments,
            'calendarDays' => $calendarDays,
            'customers' => $customers,
            'month' => $month,
            'services' => $services,
            'stats' => $stats,
            'statusLabels' => $this->statusLabels,
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.appointments.index');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_appointments');

        $validated = $this->validatedData($request);
        $customer = $this->resolveCustomer($validated);
        $vehicle = $this->resolveVehicle($validated, $customer);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'vehicle_name' => $validated['vehicle_name'] ?? $vehicle?->model,
            'license_plate' => $validated['license_plate'] ?? $vehicle?->license_plate,
            'service_id' => $validated['service_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => $validated['status'] ?? 'confirmed',
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_APPOINTMENT',
            'details' => "Tạo lịch hẹn #{$appointment->id} cho khách {$customer->name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Đã tạo lịch hẹn thành công');
    }

    public function show(Appointment $appointment)
    {
        return redirect()->route('admin.appointments.index', [
            'date' => $appointment->scheduled_at?->toDateString(),
        ]);
    }

    public function edit(Appointment $appointment)
    {
        return redirect()->route('admin.appointments.show', $appointment);
    }

    public function update(Request $request, Appointment $appointment)
    {
        Gate::authorize('manage_appointments');

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Lịch hẹn đã tiếp nhận xe nên không thể chỉnh sửa');
        }

        $validated = $this->validatedData($request, $appointment);
        $customer = $this->resolveCustomer($validated, $appointment->customer);
        $vehicle = $this->resolveVehicle($validated, $customer, $appointment->vehicle);

        $appointment->update([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'vehicle_name' => $validated['vehicle_name'] ?? $vehicle?->model,
            'license_plate' => $validated['license_plate'] ?? $vehicle?->license_plate,
            'service_id' => $validated['service_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => $validated['status'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_APPOINTMENT',
            'details' => "Cập nhật lịch hẹn #{$appointment->id}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Đã cập nhật lịch hẹn thành công');
    }

    public function convertToRo(Appointment $appointment)
    {
        Gate::authorize('create_repair_orders');

        if (in_array($appointment->status, ['cancelled', 'no_show'], true)) {
            return back()->withErrors(['error' => 'Không thể tiếp nhận lịch hẹn đã hủy hoặc khách không đến']);
        }

        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Lịch hẹn này đã được tiếp nhận trước đó']);
        }

        $appointment->loadMissing(['customer', 'vehicle', 'service']);
        $vehicle = $appointment->vehicle ?: $this->resolveVehicleFromAppointment($appointment);

        if (! $appointment->customer || ! $vehicle) {
            return back()->withErrors(['error' => 'Lịch hẹn thiếu thông tin khách hàng hoặc xe, chưa thể tạo phiếu sửa chữa']);
        }

        $servicePrice = (float) ($appointment->service?->base_price ?? 0);
        $repairOrder = RepairOrder::create([
            'track_id' => 'PSC-'.strtoupper(Str::random(8)),
            'customer_id' => $appointment->customer_id,
            'vehicle_id' => $vehicle->id,
            'advisor_id' => auth()->id(),
            'status' => RepairOrder::STATUS_PENDING,
            'quote_status' => 'draft',
            'payment_status' => 'unpaid',
            'service_type' => $appointment->service?->name ?? 'Dịch vụ theo lịch hẹn',
            'diagnosis_note' => trim(
                ($appointment->reason ? "Lý do: {$appointment->reason}. " : '').
                ($appointment->notes ? "Ghi chú: {$appointment->notes}. " : '').
                'Tiếp nhận từ lịch hẹn #'.$appointment->id
            ),
            'start_time' => now(),
            'subtotal' => $servicePrice,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $servicePrice,
        ]);

        if ($appointment->service_id) {
            RepairOrderItem::create([
                'repair_order_id' => $repairOrder->id,
                'itemable_type' => Service::class,
                'itemable_id' => $appointment->service_id,
                'name' => $appointment->service?->name ?? 'Dịch vụ theo lịch hẹn',
                'quantity' => 1,
                'unit_price' => $servicePrice,
                'subtotal' => $servicePrice,
            ]);
        }

        $appointment->update(['status' => 'completed']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CONVERT_APPOINTMENT_TO_REPAIR_ORDER',
            'details' => "Tiếp nhận lịch hẹn #{$appointment->id} thành phiếu sửa chữa {$repairOrder->track_id}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.repair_orders.show', $repairOrder)
            ->with('success', 'Đã tiếp nhận xe và tạo phiếu sửa chữa từ lịch hẹn');
    }

    public function destroy(Appointment $appointment)
    {
        Gate::authorize('manage_appointments');

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Không thể xóa lịch hẹn đã tiếp nhận xe');
        }

        $appointmentId = $appointment->id;
        $appointment->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE_APPOINTMENT',
            'details' => "Xóa lịch hẹn #{$appointmentId}",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Đã xóa lịch hẹn thành công');
    }

    private function validatedData(Request $request, ?Appointment $appointment = null): array
    {
        return $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'customer_phone' => 'required_without:customer_id|nullable|string|max:30',
            'customer_name' => 'required_without:customer_id|nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_name' => 'nullable|string|max:150',
            'license_plate' => 'nullable|string|max:30',
            'service_id' => 'nullable|exists:services,id',
            'scheduled_at' => ['required', 'date'],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels))],
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
    }

    private function resolveCustomer(array $validated, ?User $fallback = null): User
    {
        if (! empty($validated['customer_id'])) {
            return User::findOrFail($validated['customer_id']);
        }

        if (! empty($validated['customer_phone'])) {
            $customer = User::where('phone', $validated['customer_phone'])->first();
            if ($customer) {
                return $customer;
            }
        }

        if (empty($validated['customer_name']) || empty($validated['customer_phone'])) {
            if ($fallback) {
                return $fallback;
            }

            throw ValidationException::withMessages([
                'customer_phone' => 'Vui lòng chọn khách hàng hoặc nhập đủ tên và số điện thoại khách mới.',
            ]);
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

    private function resolveVehicle(array $validated, User $customer, ?Vehicle $fallback = null): ?Vehicle
    {
        if (! empty($validated['vehicle_id'])) {
            return Vehicle::findOrFail($validated['vehicle_id']);
        }

        if (! empty($validated['license_plate'])) {
            $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(REPLACE(UPPER(license_plate), '-', ''), ' ', ''), '.', '') = ?", [
                $this->normalizePlate($validated['license_plate']),
            ])->first();

            if ($vehicle) {
                $vehicle->update([
                    'user_id' => $vehicle->user_id ?: $customer->id,
                    'owner_name' => $vehicle->owner_name ?: $customer->name,
                    'owner_phone' => $vehicle->owner_phone ?: $customer->phone,
                ]);

                return $vehicle;
            }

            if (! empty($validated['vehicle_name'])) {
                return Vehicle::create([
                    'user_id' => $customer->id,
                    'license_plate' => strtoupper(trim($validated['license_plate'])),
                    'model' => $validated['vehicle_name'],
                    'type' => 'sedan',
                    'year' => date('Y'),
                    'owner_name' => $customer->name,
                    'owner_phone' => $customer->phone,
                ]);
            }
        }

        return $fallback;
    }

    private function resolveVehicleFromAppointment(Appointment $appointment): ?Vehicle
    {
        if (! $appointment->customer || ! $appointment->license_plate) {
            return null;
        }

        $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(REPLACE(UPPER(license_plate), '-', ''), ' ', ''), '.', '') = ?", [
            $this->normalizePlate($appointment->license_plate),
        ])->first();

        if ($vehicle) {
            return $vehicle;
        }

        return Vehicle::create([
            'user_id' => $appointment->customer_id,
            'license_plate' => strtoupper(trim($appointment->license_plate)),
            'model' => $appointment->vehicle_name ?: 'Chưa xác định',
            'type' => 'sedan',
            'year' => date('Y'),
            'owner_name' => $appointment->customer->name,
            'owner_phone' => $appointment->customer->phone,
        ]);
    }

    private function resolveMonth(?string $month): Carbon
    {
        if (! $month) {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    private function normalizePlate(string $plate): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($plate));
    }
}
