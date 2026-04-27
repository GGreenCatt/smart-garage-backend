<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_vehicles');

        $query = Vehicle::with(['user', 'repairOrders']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('owner_phone', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->latest()->paginate(12)->withQueryString();
        $stats = [
            'total' => Vehicle::count(),
            'in_service' => Vehicle::whereHas('repairOrders', fn ($orderQuery) => $orderQuery->whereIn('status', [
                RepairOrder::STATUS_PENDING,
                RepairOrder::STATUS_IN_PROGRESS,
                RepairOrder::STATUS_PENDING_APPROVAL,
                RepairOrder::STATUS_APPROVED,
            ]))->count(),
            'with_owner' => Vehicle::whereNotNull('user_id')->count(),
            'walk_in' => Vehicle::whereNull('user_id')->count(),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'stats'));
    }

    public function create()
    {
        Gate::authorize('manage_vehicles');

        return redirect()->route('admin.vehicles.index');
    }

    public function show($id)
    {
        Gate::authorize('manage_vehicles');

        $vehicle = Vehicle::with(['user', 'repairOrders.advisor', 'repairOrders.vehicle'])->findOrFail($id);

        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function view3d($id)
    {
        Gate::authorize('manage_vehicles');

        $vehicle = Vehicle::findOrFail($id);
        $backUrl = route('admin.vehicles.show', $id);

        return view('staff.vehicle.inspection', compact('vehicle', 'backUrl'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_vehicles');

        $validated = $request->validate([
            'license_plate' => ['required', 'string', 'max:30', Rule::unique('vehicles', 'license_plate')],
            'make' => 'nullable|string|max:100',
            'model' => 'required|string|max:150',
            'type' => 'required|string|max:50',
            'vin' => 'nullable|string|max:50',
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'nullable|string|max:30',
        ]);

        $user = null;
        if (! empty($validated['owner_phone'])) {
            $user = User::where('phone', $validated['owner_phone'])
                ->where(function ($query) {
                    $query->where('role', 'customer')
                        ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->where('slug', 'customer'));
                })
                ->first();
        }

        $vehicle = Vehicle::create(array_merge($validated, [
            'user_id' => $user?->id,
        ]));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_VEHICLE',
            'details' => "Đăng ký xe {$vehicle->license_plate} cho {$vehicle->owner_name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'Đăng ký xe thành công');
    }
}
