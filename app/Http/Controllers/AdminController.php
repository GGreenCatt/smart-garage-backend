<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\RepairOrder;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_vehicles' => Vehicle::count(),
            'active_orders' => RepairOrder::where('status', '!=', 'completed')->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'monthly_revenue' => 150000000, // Mocked for now
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
