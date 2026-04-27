<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\RepairOrder;
use App\Models\Vehicle;

class CustomerController extends Controller
{
    // Landing Page
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('customer.home');
    }

    // Customer Dashboard (Auth required later)
    public function dashboard()
    {
        $user = auth()->user();
        $orders = collect();
        $vehicles = collect();
        $appointments = collect();

        if ($user && $user->role === 'customer') {
            $vehicles = Vehicle::query()
                ->where('user_id', $user->id)
                ->when($user->phone, function ($query) use ($user) {
                    $query->orWhere('owner_phone', $user->phone);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('id')
                ->values();

            $orders = RepairOrder::with([
                    'vehicle',
                    'tasks.children.items',
                    'tasks.items',
                    'items',
                    'vhcReport',
                ])
                ->where(function ($query) use ($user) {
                    $query->where('customer_id', $user->id)
                        ->when($user->phone, function ($phoneQuery) use ($user) {
                            $phoneQuery->orWhereHas('vehicle', function ($vehicleQuery) use ($user) {
                                $vehicleQuery->where('owner_phone', $user->phone);
                            });
                        });
                })
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get()
                ->unique('id')
                ->values();

            $appointments = Appointment::with(['vehicle', 'service'])
                ->where('customer_id', $user->id)
                ->orderBy('scheduled_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('customer.dashboard', compact('orders', 'vehicles', 'appointments'));
    }

    public function myOrders()
    {
        $user = auth()->user();
        $orders = $this->customerOrdersQuery($user)
            ->with(['vehicle', 'advisor'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function showOrder($id)
    {
        $order = $this->customerOrdersQuery(auth()->user())
            ->with(['vehicle', 'advisor', 'items', 'items.itemable', 'tasks.items', 'promotion'])
            ->findOrFail($id);

        return view('customer.orders.show', compact('order'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('customer.profile.index', compact('user'));
    }

    public function myVehicles()
    {
        $user = auth()->user();
        $vehicles = collect();
        
        if ($user) {
            $vehicles = $this->customerVehiclesQuery($user)->get();
        }

        return view('customer.vehicles.index', compact('vehicles'));
    }

    // 3D View for a specific vehicle
    public function vehicleDetail($id)
    {
        $vehicle = $this->customerVehiclesQuery(auth()->user())->findOrFail($id);
        $backUrl = route('customer.dashboard');
        return view('customer.vehicle.3d_view', compact('vehicle', 'backUrl'));
    }

    public function getVehicleInspection($id, \Illuminate\Http\Request $request)
    {
        $vehicle = $this->customerVehiclesQuery(auth()->user())->findOrFail($id);

        $orderId = $request->input('order_id');
        
        if ($orderId) {
            $order = $this->customerOrdersQuery(auth()->user())
                        ->where('id', $orderId)
                        ->where('vehicle_id', $vehicle->id)
                        ->first();
        } else {
            // Find latest active order
            $order = $this->customerOrdersQuery(auth()->user())
                        ->where('vehicle_id', $vehicle->id)
                        ->where('status', '!=', 'completed')
                        ->latest()
                        ->first();
        }

        if (!$order) return response()->json(['defects' => []]);

        $report = \App\Models\VhcReport::where('repair_order_id', $order->id)
                    ->where('status', 'published') // ONLY Published
                    ->first();

        if (!$report) return response()->json(['defects' => []]);

        return response()->json(['defects' => $report->defects]);
    }

    private function customerOrdersQuery($user)
    {
        return RepairOrder::query()
            ->where(function ($query) use ($user) {
                $query->where('customer_id', $user->id)
                    ->when($user->phone, function ($phoneQuery) use ($user) {
                        $phoneQuery->orWhereHas('vehicle', function ($vehicleQuery) use ($user) {
                            $vehicleQuery->where('owner_phone', $user->phone);
                        });
                    });
            });
    }

    private function customerVehiclesQuery($user)
    {
        return Vehicle::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->when($user->phone, function ($phoneQuery) use ($user) {
                        $phoneQuery->orWhere('owner_phone', $user->phone);
                    });
            })
            ->orderBy('created_at', 'desc');
    }

}
