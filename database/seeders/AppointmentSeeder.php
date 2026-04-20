<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Service;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $services = Service::all();

        if($customers->isEmpty()) return;

        // 1. Create some PAST appointments (Completed/Cancelled)
        foreach(range(1, 10) as $i) {
            $customer = $customers->random();
            $vehicle = Vehicle::where('user_id', $customer->id)->inRandomOrder()->first();
            
            Appointment::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle ? $vehicle->id : null,
                'service_id' => $services->isNotEmpty() ? $services->random()->id : null,
                'scheduled_at' => Carbon::now()->subDays(rand(1, 30))->setHour(rand(8, 17)),
                'status' => rand(0, 1) ? 'completed' : 'cancelled',
                'notes' => 'Lịch hẹn cũ demo',
                'admin_notes' => 'Đã xử lý xong'
            ]);
        }

        // 2. Create FUTURE appointments (Pending/Confirmed)
        foreach(range(1, 10) as $i) {
            $customer = $customers->random();
            $vehicle = Vehicle::where('user_id', $customer->id)->inRandomOrder()->first();
            
            Appointment::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle ? $vehicle->id : null,
                'service_id' => $services->isNotEmpty() ? $services->random()->id : null,

                'scheduled_at' => Carbon::now()->addDays(rand(1, 14))->setHour(rand(8, 17)),
                'status' => rand(0, 1) ? 'pending' : 'confirmed',
                'notes' => 'Khách muốn làm gấp',
            ]);
        }
    }
}
