<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Vehicle;
use App\Models\User;

use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_vehicles');
        $query = Vehicle::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%") // Existing column
                  ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->latest()->paginate(12);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['user', 'repairOrders.advisor'])->findOrFail($id);
        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function view3d($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $backUrl = route('admin.vehicles.show', $id);
        // Reuse the customer 3D view but potentially we'll need to adjust layout/back link
        return view('staff.vehicle.inspection', compact('vehicle', 'backUrl'));
    }

    public function store(Request $request)
    {
        // Basic implementation for quick add
        $validated = $request->validate([
             'license_plate' => 'required|unique:vehicles',
             'model' => 'required',
             'type' => 'required',
             'vin' => 'nullable|string',
             'owner_name' => 'required|string',
             'owner_phone' => 'required|string'
        ]);
        
        // Find owner by phone if provided
        $userId = null;
        if(!empty($validated['owner_phone'])) {
             $user = User::where('phone', $validated['owner_phone'])->first();
             $userId = $user ? $user->id : null;
        }

        Vehicle::create(array_merge($validated, ['user_id' => $userId]));

        return back()->with('success', 'Vehicle added successfully');
    }
}
