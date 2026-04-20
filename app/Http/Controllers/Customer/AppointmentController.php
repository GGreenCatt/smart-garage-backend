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
        $vehicles = Vehicle::where('customer_id', Auth::id())->get();
        return view('customer.appointments.book', compact('services', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_id' => 'required|exists:services,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string'
        ]);

        Appointment::create([
            'customer_id' => Auth::id(),
            'vehicle_id' => $validated['vehicle_id'],
            'service_id' => $validated['service_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'],
            'status' => 'pending'
        ]);

        return redirect()->route('customer.appointments.index')->with('success', 'Đặt lịch thành công! Vui lòng chờ xác nhận.');
    }
}
