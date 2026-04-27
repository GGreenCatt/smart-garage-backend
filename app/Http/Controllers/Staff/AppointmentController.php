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
use Illuminate\Support\Facades\Schema;

class AppointmentController extends Controller
{
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
                        ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                            $customerQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($keyword) {
                            $vehicleQuery
                                ->where('license_plate', 'like', "%{$keyword}%")
                                ->orWhere('model', 'like', "%{$keyword}%");
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
        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,confirmed,cancelled,completed',
            'scheduled_at' => 'sometimes|required|date',
            'service_id' => 'nullable|exists:services,id',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Cập nhật lịch hẹn thành công');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return back()->with('success', 'Đã xóa lịch hẹn thành công');
    }

    public function convertToRo(Appointment $appointment)
    {
        if ($appointment->status === 'cancelled') {
            return back()->withErrors(['error' => 'Không thể tạo lệnh cho lịch đã hủy']);
        }

        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Lịch hẹn này đã được chuyển thành lệnh sửa chữa.']);
        }

        $appointment->loadMissing(['customer', 'vehicle', 'service']);
        $vehicleId = $appointment->vehicle_id;

        if (! $vehicleId) {
            if (! $appointment->customer || ! $appointment->license_plate) {
                return back()->withErrors(['error' => 'Lịch hẹn thiếu xe hoặc biển số, không thể tạo lệnh sửa chữa.']);
            }

            $plate = strtoupper(trim($appointment->license_plate));
            $normalizedPlate = preg_replace('/[^A-Z0-9]/', '', $plate);
            $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(REPLACE(UPPER(license_plate), '-', ''), ' ', ''), '.', '') = ?", [$normalizedPlate])->first();

            if (! $vehicle) {
                $vehicle = Vehicle::create([
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

            $vehicleId = $vehicle->id;
        }

        $servicePrice = (float) ($appointment->service?->base_price ?? $appointment->service?->price ?? 0);

        $ro = RepairOrder::create([
            'track_id' => strtoupper(uniqid('RO-')),
            'customer_id' => $appointment->customer_id,
            'vehicle_id' => $vehicleId,
            'advisor_id' => Auth::id(),
            'status' => 'pending',
            'service_type' => $appointment->service->name ?? 'Dịch vụ theo lịch hẹn',
            'diagnosis_note' => ($appointment->reason ? 'Lý do: ' . $appointment->reason . ' | ' : '') .
                ($appointment->notes ? 'Ghi chú: ' . $appointment->notes . ' | ' : '') .
                'Đặt lịch trước: ' . ($appointment->service->name ?? 'Dịch vụ'),
            'start_time' => now(),
        ]);

        if ($appointment->service_id) {
            RepairOrderItem::create([
                'repair_order_id' => $ro->id,
                'itemable_type' => Service::class,
                'itemable_id' => $appointment->service_id,
                'name' => $appointment->service->name ?? 'Dịch vụ theo lịch hẹn',
                'quantity' => 1,
                'unit_price' => $servicePrice,
                'subtotal' => $servicePrice,
            ]);

            $ro->subtotal = $servicePrice;
            $ro->total_amount = $ro->subtotal;
            $ro->save();
        }

        $appointment->update(['status' => 'completed']);

        return redirect()->route('staff.order.show', $ro->id)
            ->with('success', 'Đã tiếp nhận xe và tạo lệnh sửa chữa từ lịch hẹn');
    }
}
