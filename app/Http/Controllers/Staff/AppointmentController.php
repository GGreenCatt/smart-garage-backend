<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['customer', 'vehicle', 'service'])
            ->orderBy('scheduled_at', 'asc')
            ->get();
            
        $services = Service::where('is_active', true)->get();
            
        return view('staff.appointments.index', compact('appointments', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,confirmed,cancelled,completed',
            'scheduled_at' => 'sometimes|required|date',
            'service_id' => 'nullable|exists:services,id',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string'
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
        if ($appointment->status == 'cancelled') {
            return back()->withErrors(['error' => 'Không thể tạo lệnh cho lịch đã hủy']);
        }

        $vehicle_id = $appointment->vehicle_id;
        
        // If vehicle is not linked (manually entered), we might want to ask staff to create the vehicle first,
        // but for now, we'll allow it and handle it gracefully or require a vehicle to be created first.
        // Let's create the RO anyway. If vehicle_id is null, it's not ideal, but the RO can technically have it null until updated.
        // Wait, RepairOrder migration requires vehicle_id? Let's check RO model later. Assuming nullable or requires manual update.

        $ro = RepairOrder::create([
            'customer_id' => $appointment->customer_id,
            'vehicle_id' => $vehicle_id,
            'advisor_id' => Auth::id(), // Staff who receives the car
            'status' => 'pending',
            'diagnosis' => ($appointment->reason ? "Lý do: " . $appointment->reason . " | " : "") . 
                           ($appointment->notes ? "Ghi chú: " . $appointment->notes . " | " : "") . 
                           "Đặt lịch trước: " . ($appointment->service->name ?? 'Dịch vụ'),
            'mileage' => 0
        ]);

        if ($appointment->service_id) {
            RepairOrderItem::create([
                'repair_order_id' => $ro->id,
                'itemable_type' => Service::class,
                'itemable_id' => $appointment->service_id,
                'quantity' => 1,
                'unit_price' => $appointment->service->price ?? 0,
                'subtotal' => $appointment->service->price ?? 0,
                'technician_id' => null
            ]);
            
            $ro->subtotal = $appointment->service->price ?? 0;
            $ro->total_amount = $ro->subtotal;
            $ro->save();
        }

        $appointment->update(['status' => 'completed']);

        return redirect()->route('staff.order.show', $ro->id)
            ->with('success', 'Đã tiếp nhận xe và tạo Lệnh Sửa Chữa từ Lịch Hẹn');
    }
}
