<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Simulated Auth User or Guest
        $user = auth()->user();
        $orders = collect(); // Empty by default

        if($user && $user->role === 'customer') {
            // Fetch by customer ID (link via User) -> RepairOrder usually links to Vehicle, Vehicle links to Owner Phone?
            // For MVP, assuming User has 'phone', Vehicle has 'owner_phone'.
            // Or better: User -> vehicles() -> repairOrders() if relations existed.
            // Let's rely on Phone Number sync as requested by user previously.
            
            // Assuming User model has 'phone' field or we use email/name fallback.
            // Let's try to match Vehicles by owner_phone = user->email (just as a unique key for demo) or user->phone.
            // Since User table migration might be basic, let's assume we match by 'owner_phone'.
            
            $phone = $user->email; // Using email as phone placeholder for this specific user in demo '0909999999'?
            // Actually let's just fetch all for now or mock it.
            // Better: Fetch orders where vehicle->owner_phone matches user?
            // Let's implement a simple lookup.
            if ($user->phone) {
                \Illuminate\Support\Facades\Log::info('Dashboard: User Phone ' . $user->phone);
                $orders = \App\Models\RepairOrder::with('vehicle')
                    ->whereHas('vehicle', function($q) use ($user) {
                         $q->where('owner_phone', $user->phone); // Match by phone
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                \Illuminate\Support\Facades\Log::info('Dashboard: Orders Found ' . $orders->count());
            } else {
                 \Illuminate\Support\Facades\Log::info('Dashboard: No Phone for User ' . $user->id);
                 // Fallback or empty
                 $orders = collect();
            }
        }

        return view('customer.dashboard', compact('orders'));
    }

    public function myOrders()
    {
        $user = auth()->user();
        $orders = collect();

        if ($user && $user->phone) {
            $orders = \App\Models\RepairOrder::with('vehicle')
                ->whereHas('vehicle', function($q) use ($user) {
                        $q->where('owner_phone', $user->phone);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('customer.orders.index', compact('orders'));
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
        
        if ($user && $user->phone) {
            // Find vehicles where owner_phone matches user phone
            // In a real relation this would be $user->vehicles
            $vehicles = \App\Models\Vehicle::where('owner_phone', $user->phone)->get();
        }

        return view('customer.vehicles.index', compact('vehicles'));
    }

    // 3D View for a specific vehicle
    public function vehicleDetail($id)
    {
        // In real app, check if user owns this vehicle
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $backUrl = route('customer.dashboard');
        return view('customer.vehicle.3d_view', compact('vehicle', 'backUrl'));
    }

    public function getVehicleInspection($id, \Illuminate\Http\Request $request)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);

        $orderId = $request->input('order_id');
        
        if ($orderId) {
            $order = \App\Models\RepairOrder::where('id', $orderId)
                        ->where('vehicle_id', $vehicle->id)
                        ->first();
        } else {
            // Find latest active order
            $order = \App\Models\RepairOrder::where('vehicle_id', $vehicle->id)
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

    public function approveQuote($id)
    {
        $order = \App\Models\RepairOrder::with('vehicle')->findOrFail($id);
        $order->update(['quote_status' => 'approved']);
        
        // Approve all individual items
        $order->items()->update(['status' => 'approved']);

        // Notify Advisor or all staff
        $this->notifyStaff($order, 'approved');
        
        return response()->json(['success' => true]);
    }

    public function rejectQuote($id)
    {
        $order = \App\Models\RepairOrder::with('vehicle')->findOrFail($id);
        $order->update(['quote_status' => 'rejected']);
        
        // Reject all individual items (or keep pending, but 'rejected' is clearer)
        $order->items()->update(['status' => 'rejected']);

        // Notify Advisor or all staff
        $this->notifyStaff($order, 'rejected');
        
        return response()->json(['success' => true]);
    }

    protected function notifyStaff($order, $status)
    {
        $advisor = \App\Models\User::find($order->advisor_id);
        $notification = new \App\Notifications\QuoteResponseNotification($order, $status);

        if ($advisor) {
            $advisor->notify($notification);
        } else {
            // Notify all staff if no advisor assigned
            $staffs = \App\Models\User::where('role', 'staff')->get();
            foreach ($staffs as $staff) {
                $staff->notify($notification);
            }
        }
    }
}
