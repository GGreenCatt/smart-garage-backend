<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    private array $editableStatuses = ['pending', 'confirmed', 'cancelled', 'no_show'];

    public function index(Request $request)
    {
        $appointments = Appointment::with(['customer', 'vehicle', 'service'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('scheduled_at', $request->date))
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->q);

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->where('license_plate', 'like', "%{$keyword}%")
                        ->orWhere('vehicle_name', 'like', "%{$keyword}%")
                        ->orWhere('reason', 'like', "%{$keyword}%")
                        ->orWhere('notes', 'like', "%{$keyword}%")
                        ->orWhere('admin_notes', 'like', "%{$keyword}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                            $customerQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($keyword) {
                            $vehicleQuery
                                ->where('license_plate', 'like', "%{$keyword}%")
                                ->orWhere('model', 'like', "%{$keyword}%")
                                ->orWhere('make', 'like', "%{$keyword}%");
                        });
                });
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $services = Service::query()
            ->when(Schema::hasColumn('services', 'is_active'), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        return view('staff.appointments.index', compact('appointments', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Lịch hẹn đã tiếp nhận xe nên không thể chỉnh sửa.']);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'required', Rule::in($this->editableStatuses)],
            'scheduled_at' => 'sometimes|required|date',
            'service_id' => 'nullable|exists:services,id',
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Cập nhật lịch hẹn thành công');
    }

    public function destroy(Appointment $appointment)
    {
        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Không thể xóa lịch hẹn đã tiếp nhận xe.']);
        }

        $appointment->delete();

        return back()->with('success', 'Đã xóa lịch hẹn thành công');
    }

    public function convertToRo(Appointment $appointment)
    {
        if (in_array($appointment->status, ['cancelled', 'no_show'], true)) {
            return back()->withErrors(['error' => 'Không thể tạo lệnh cho lịch đã hủy hoặc khách không đến.']);
        }

        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Lịch hẹn này đã được chuyển thành lệnh sửa chữa.']);
        }

        $appointment->loadMissing(['customer', 'vehicle', 'service']);

        if (! $appointment->customer) {
            return back()->withErrors(['error' => 'Lịch hẹn thiếu thông tin khách hàng, không thể tạo lệnh sửa chữa.']);
        }

        $vehicle = $appointment->vehicle ?: $this->resolveVehicleFromAppointment($appointment);
        if (! $vehicle) {
            return back()->withErrors(['error' => 'Lịch hẹn thiếu xe hoặc biển số, không thể tạo lệnh sửa chữa.']);
        }

        $servicePrice = (float) ($appointment->service?->base_price ?? $appointment->service?->price ?? 0);

        $ro = DB::transaction(function () use ($appointment, $vehicle, $servicePrice) {
            $ro = RepairOrder::create([
                'track_id' => strtoupper(uniqid('RO-')),
                'customer_id' => $appointment->customer_id,
                'vehicle_id' => $vehicle->id,
                'advisor_id' => Auth::id(),
                'status' => RepairOrder::STATUS_PENDING,
                'quote_status' => 'draft',
                'payment_status' => 'unpaid',
                'service_type' => $appointment->service?->name ?? 'Dịch vụ theo lịch hẹn',
                'diagnosis_note' => $this->buildDiagnosisNote($appointment),
                'start_time' => now(),
                'subtotal' => $servicePrice,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $servicePrice,
            ]);

            if ($appointment->service_id) {
                RepairOrderItem::create([
                    'repair_order_id' => $ro->id,
                    'itemable_type' => Service::class,
                    'itemable_id' => $appointment->service_id,
                    'name' => $appointment->service?->name ?? 'Dịch vụ theo lịch hẹn',
                    'quantity' => 1,
                    'unit_price' => $servicePrice,
                    'subtotal' => $servicePrice,
                ]);
            }

            $appointment->update(['status' => 'completed']);

            return $ro;
        });

        return redirect()->route('staff.order.show', $ro->id)
            ->with('success', 'Đã tiếp nhận xe và tạo lệnh sửa chữa từ lịch hẹn');
    }

    private function resolveVehicleFromAppointment(Appointment $appointment): ?Vehicle
    {
        if (! $appointment->license_plate) {
            return null;
        }

        $plate = strtoupper(trim($appointment->license_plate));
        $normalizedPlate = $this->normalizePlate($plate);

        $vehicle = Vehicle::where(function ($query) use ($appointment) {
                $query->where('user_id', $appointment->customer_id)
                    ->orWhere('owner_phone', $appointment->customer?->phone);
            })
            ->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(license_plate), '-', ''), ' ', ''), '.', '') = ?", [$normalizedPlate])
            ->first();

        if ($vehicle) {
            return $vehicle;
        }

        return Vehicle::create([
            'user_id' => $appointment->customer_id,
            'license_plate' => $plate,
            'model' => $appointment->vehicle_name ?: 'Chưa xác định',
            'type' => 'sedan',
            'year' => date('Y'),
            'color' => 'Unknown',
            'owner_name' => $appointment->customer->name,
            'owner_phone' => $appointment->customer->phone,
        ]);
    }

    private function buildDiagnosisNote(Appointment $appointment): string
    {
        return trim(
            ($appointment->reason ? "Lý do: {$appointment->reason}. " : '').
            ($appointment->notes ? "Ghi chú khách: {$appointment->notes}. " : '').
            ($appointment->admin_notes ? "Phản hồi garage: {$appointment->admin_notes}. " : '').
            'Tiếp nhận từ lịch hẹn #'.$appointment->id
        );
    }

    private function normalizePlate(string $plate): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($plate));
    }
}
