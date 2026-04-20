<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\SosRequest;
use Illuminate\Support\Facades\DB;

class SosSeeder extends Seeder
{
    public function run(): void
    {
        // Garage Center (Demo: Saigon Centre)
        $centerLat = 10.7769;
        $centerLng = 106.7009;

        // 1. Update Staff Locations (simulate them being somewhat near)
        $staffs = User::where('role', 'staff')->get();
        foreach ($staffs as $index => $staff) {
            // Random offset tiny (within 20 meters of garage)
            $latOffset = (rand(-5, 5) / 100000); 
            $lngOffset = (rand(-5, 5) / 100000); 
            
            $staff->update([
                'latitude' => $centerLat + $latOffset,
                'longitude' => $centerLng + $lngOffset,
                'last_location_update' => now(),
                // 'check_in_status' => 'checked_in' // Removed as column doesn't exist
            ]);
        }

        // 2. Clear old SOS
        DB::table('sos_requests')->truncate();
        
        $customers = User::where('role', 'customer')->take(5)->get();
        if($customers->isEmpty()) {
             // Fallback if no customers
             $customers = User::where('role', 'admin')->get(); // Just to have someone
        }

        // 3. Create SOS Requests
        
        // Case A: Pending SOS (Red alert)
        if (isset($customers[0])) {
            SosRequest::create([
                'customer_id' => $customers[0]->id,
                'latitude' => $centerLat + 0.015, // A bit far North
                'longitude' => $centerLng + 0.005,
                'description' => 'Xe chết máy giữa đường, cần kích bình gấp!',
                'status' => 'pending',
                'created_at' => now()->subMinutes(5)
            ]);
        }

        // Case B: Assigned/In Progress SOS (Show routing)
        if (isset($customers[1]) && $staffs->isNotEmpty()) {
            $staff = $staffs->first();
            
            // Set Staff position to be "On the road" (Midway between Garage and Customer)
            // Customer is at roughly Garage + 0.02 lat, 0.02 lng
            // Let's put staff at Garage + 0.01 lat, 0.01 lng
            $staff->update([
                'latitude' => $centerLat + 0.010,
                'longitude' => $centerLng + 0.008,
                'last_location_update' => now()
            ]);

            SosRequest::create([
                'customer_id' => $customers[1]->id,
                'latitude' => $centerLat + 0.020, // Customer further away
                'longitude' => $centerLng + 0.015,
                'description' => 'Nổ lốp xe, cần thay bánh dự phòng',
                'status' => 'in_progress',
                'assigned_staff_id' => $staff->id,
                'created_at' => now()->subMinutes(15)
            ]);
        }
    }
}
