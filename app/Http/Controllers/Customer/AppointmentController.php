<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('customer_id', Auth::id())
            ->orderBy('scheduled_at', 'desc')
            ->get();
        return view('customer.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $services = Service::all();
        $vehicles = Vehicle::where('user_id', Auth::id())->get();
        return view('customer.appointments.book', compact('services', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_name' => 'required_without:vehicle_id|nullable|string|max:255',
            'license_plate' => 'required_without:vehicle_id|nullable|string|max:50',
            'service_id' => 'nullable|exists:services,id',
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        Appointment::create([
            'customer_id' => Auth::id(),
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'vehicle_name' => $validated['vehicle_name'] ?? null,
            'license_plate' => $validated['license_plate'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending'
        ]);

        return redirect()->route('customer.appointments.index')->with('success', 'Đặt lịch thành công! Vui lòng chờ xác nhận.');
    }

    public function edit($id)
    {
        $appointment = Appointment::where('customer_id', Auth::id())->findOrFail($id);
        if ($appointment->status !== 'pending') {
            return redirect()->route('customer.appointments.index')->with('error', 'Chỉ có thể sửa lịch hẹn đang chờ xác nhận.');
        }
        $services = Service::all();
        $vehicles = Vehicle::where('user_id', Auth::id())->get();
        return view('customer.appointments.edit', compact('appointment', 'services', 'vehicles'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::where('customer_id', Auth::id())->findOrFail($id);
        if ($appointment->status !== 'pending') {
            return redirect()->route('customer.appointments.index')->with('error', 'Chỉ có thể sửa lịch hẹn đang chờ xác nhận.');
        }

        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_name' => 'required_without:vehicle_id|nullable|string|max:255',
            'license_plate' => 'required_without:vehicle_id|nullable|string|max:50',
            'service_id' => 'nullable|exists:services,id',
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $appointment->update([
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'vehicle_name' => $validated['vehicle_name'] ?? null,
            'license_plate' => $validated['license_plate'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('customer.appointments.index')->with('success', 'Cập nhật lịch hẹn thành công!');
    }

    public function destroy($id)
    {
        $appointment = Appointment::where('customer_id', Auth::id())->findOrFail($id);
        if ($appointment->status === 'pending') {
            $appointment->update(['status' => 'cancelled']);
            return back()->with('success', 'Đã hủy lịch hẹn thành công.');
        }
        return back()->with('error', 'Không thể hủy lịch hẹn đã được xác nhận hoặc hoàn thành.');
    }
}
