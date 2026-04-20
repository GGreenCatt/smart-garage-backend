<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_appointments');
        
        $appointments = Appointment::with(['customer', 'vehicle', 'service'])
            ->orderBy('scheduled_at', 'asc')
            ->get();
            
        return view('admin.appointments.index', compact('appointments'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_appointments');
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'service_id' => 'nullable|exists:services,id',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        Appointment::create($validated);
        return back()->with('success', 'Đã tạo lịch hẹn thành công');
    }

    public function update(Request $request, Appointment $appointment)
    {
        Gate::authorize('manage_appointments');
        
        $appointment->update($request->only(['status', 'admin_notes']));
        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    public function convertToRo(Appointment $appointment)
    {
        Gate::authorize('create_repair_orders');
        
        if ($appointment->status == 'cancelled') {
            return back()->withErrors(['error' => 'Không thể tạo lệnh cho lịch đã hủy']);
        }

        // Create RO
        $ro = \App\Models\RepairOrder::create([
            'customer_id' => $appointment->customer_id,
            'vehicle_id' => $appointment->vehicle_id,
            'advisor_id' => auth()->id(),
            'status' => 'pending',
            'diagnosis' => $appointment->notes ?? 'Đặt lịch trước: ' . ($appointment->service->name ?? 'Dịch vụ'),
            'mileage' => 0 // Default, needs update
        ]);

        // If service selected, add it
        if ($appointment->service_id) {
            \App\Models\RepairOrderItem::create([
                'repair_order_id' => $ro->id,
                'itemable_type' => \App\Models\Service::class,
                'itemable_id' => $appointment->service_id,
                'quantity' => 1,
                'unit_price' => $appointment->service->price ?? 0,
                'subtotal' => $appointment->service->price ?? 0,
                'technician_id' => null
            ]);
            
            // Recalculate totals (Simplified version of what's in RO Controller)
            $ro->subtotal = $appointment->service->price ?? 0;
            $ro->total_amount = $ro->subtotal;
            $ro->save();
        }

        // Update Appointment
        $appointment->update(['status' => 'completed']);

        return redirect()->route('admin.repair_orders.show', $ro->id)
            ->with('success', 'Đã tạo Lệnh Sửa Chữa từ Lịch Hẹn');
    }

    public function destroy(Appointment $appointment)
    {
        Gate::authorize('manage_appointments');
        $appointment->delete();
        return back()->with('success', 'Đã hủy lịch hẹn');
    }
}
