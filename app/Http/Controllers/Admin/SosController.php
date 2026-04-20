<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SosRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class SosController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_sos');
        return view('admin.sos.index');
    }

    // API for Map Polling
    public function getMapData()
    {
        Gate::authorize('manage_sos');

        // 1. Active SOS Requests
        $sosRequests = SosRequest::whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with(['customer', 'assignedStaff', 'vehicle'])
            ->get();

        // 2. Get IDs of staff currently assigned to active SOS
        $busyStaffIds = $sosRequests->pluck('assigned_staff_id')->filter()->unique();

        // 3. Active Staff Locations (Only show those busy with SOS as requested)
        $staffs = User::whereIn('id', $busyStaffIds)
            ->whereNotNull('latitude')
            ->get(['id', 'name', 'latitude', 'longitude', 'role', 'last_location_update']);

        return response()->json([
            'sos' => $sosRequests,
            'staffs' => $staffs,
            'garage' => [
                'lat' => 10.7769, // Saigorn Centre (Demo)
                'lng' => 106.7009
            ]
        ]);
    }

    // API for Staff/App to update location
    public function updateLocation(Request $request) 
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        $user = auth()->user();
        $user->update([
            'latitude' => $request->lat,
            'longitude' => $request->lng,
            'last_location_update' => now()
        ]);

        return response()->json(['status' => 'ok']);
    }
}
