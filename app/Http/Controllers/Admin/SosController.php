<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SosRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SosController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_sos');

        $stats = [
            'pending' => SosRequest::where('status', 'pending')->count(),
            'active' => SosRequest::whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed_today' => SosRequest::where('status', 'completed')->whereDate('completed_at', today())->count(),
            'cancelled_today' => SosRequest::where('status', 'cancelled')->whereDate('cancelled_at', today())->count(),
        ];

        return view('admin.sos.index', compact('stats'));
    }

    public function getMapData()
    {
        Gate::authorize('manage_sos');

        $sosRequests = SosRequest::whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['customer:id,name,phone', 'assignedStaff:id,name,phone,latitude,longitude,last_location_update', 'vehicle:id,license_plate,make,model'])
            ->latest()
            ->get()
            ->filter(fn (SosRequest $request) => $this->hasValidCoordinates($request->latitude, $request->longitude))
            ->map(fn (SosRequest $request) => $this->sosPayload($request))
            ->values();

        $recentRequests = SosRequest::whereIn('status', ['completed', 'cancelled'])
            ->with(['customer:id,name,phone', 'assignedStaff:id,name,phone,latitude,longitude,last_location_update', 'vehicle:id,license_plate,make,model'])
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (SosRequest $request) => $this->sosPayload($request))
            ->values();

        $staffs = User::where('is_sharing_location', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('last_location_update', '>=', now()->subMinutes(15))
            ->where(function ($query) {
                $query->whereIn('role', ['staff', 'technician', 'manager'])
                    ->orWhereHas('assignedRole', fn ($roleQuery) => $roleQuery->whereNotIn('slug', ['admin', 'customer']));
            })
            ->get(['id', 'name', 'phone', 'latitude', 'longitude', 'role', 'last_location_update'])
            ->filter(fn (User $staff) => $this->hasValidCoordinates($staff->latitude, $staff->longitude))
            ->map(function (User $staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'phone' => $staff->phone,
                    'latitude' => $staff->latitude,
                    'longitude' => $staff->longitude,
                    'role' => $staff->role,
                    'last_location_update' => optional($staff->last_location_update)->diffForHumans(),
                ];
            })
            ->values();

        return response()->json([
            'sos' => $sosRequests,
            'recent' => $recentRequests,
            'staffs' => $staffs,
            'garage' => [
                'lat' => (float) Setting::get('garage_latitude', 10.7769),
                'lng' => (float) Setting::get('garage_longitude', 106.7009),
                'name' => Setting::get('garage_name', 'Smart Garage'),
            ],
        ]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $request->user()->update([
            'latitude' => $request->lat,
            'longitude' => $request->lng,
            'is_sharing_location' => true,
            'last_location_update' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function statusLabel(string $status): string
    {
        return [
            'pending' => 'Chờ tiếp nhận',
            'assigned' => 'Đã phân công',
            'in_progress' => 'Đang cứu hộ',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ][$status] ?? $status;
    }

    private function sosPayload(SosRequest $request): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status,
            'status_label' => $this->statusLabel($request->status),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'description' => $request->description,
            'created_at' => $request->created_at?->diffForHumans(),
            'created_at_full' => $request->created_at?->format('H:i d/m/Y'),
            'completed_at' => $request->completed_at?->format('H:i d/m/Y'),
            'cancelled_at' => $request->cancelled_at?->format('H:i d/m/Y'),
            'display_name' => $request->display_name,
            'display_phone' => $request->display_phone,
            'vehicle' => $request->vehicle ? [
                'license_plate' => $request->vehicle->license_plate,
                'name' => trim(($request->vehicle->make ?? '').' '.($request->vehicle->model ?? '')),
            ] : null,
            'assigned_staff' => $request->assignedStaff ? [
                'id' => $request->assignedStaff->id,
                'name' => $request->assignedStaff->name,
                'phone' => $request->assignedStaff->phone,
                'latitude' => $request->assignedStaff->latitude,
                'longitude' => $request->assignedStaff->longitude,
                'last_location_update' => optional($request->assignedStaff->last_location_update)->diffForHumans(),
            ] : null,
        ];
    }

    private function hasValidCoordinates($latitude, $longitude): bool
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;

        return $lat >= -90 && $lat <= 90
            && $lng >= -180 && $lng <= 180
            && ! ($lat === 0.0 && $lng === 0.0);
    }
}
