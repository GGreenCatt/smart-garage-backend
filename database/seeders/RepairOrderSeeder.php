<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Part;
use Illuminate\Support\Str;

class RepairOrderSeeder extends Seeder
{
    public function run(): void
    {
        $advisor = User::where('role', 'admin')->orWhere('role', 'staff')->first();
        $customer = User::where('email', 'customer@smartgarage.com')->first();
        $vehicle1 = Vehicle::where('license_plate', '30F-567.89')->first();
        $vehicle2 = Vehicle::where('license_plate', '51H-999.99')->first();

        $serviceOil = Service::where('code', 'SVC-OIL-01')->first();
        $partOil = Part::where('sku', 'OIL-CAS-001')->first();

        if (!$advisor || !$customer || !$vehicle1) return;

        // RO 1: Completed Oil Change
        $ro1 = RepairOrder::create([
            'track_id' => 'RO-' . Str::upper(Str::random(8)),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle1->id,
            'advisor_id' => $advisor->id,
            'status' => 'completed',
            'odometer_reading' => 15000,
            'diagnosis_note' => 'Xe báo thay dầu định kỳ',
            'expected_completion_date' => now()->subDay(),
            'total_amount' => 0 // will update
        ]);

        if ($serviceOil) {
            $ro1->items()->create([
                'itemable_type' => Service::class,
                'itemable_id' => $serviceOil->id,
                'quantity' => 1,
                'unit_price' => $serviceOil->base_price,
                'subtotal' => $serviceOil->base_price
            ]);
        }
        if ($partOil) {
            $ro1->items()->create([
                'itemable_type' => Part::class,
                'itemable_id' => $partOil->id,
                'quantity' => 4, // 4 liters
                'unit_price' => $partOil->selling_price,
                'subtotal' => $partOil->selling_price * 4
            ]);
        }
        $ro1->update(['total_amount' => $ro1->items()->sum('subtotal')]);

        // RO 2: Pending Checkup
        if ($vehicle2) {
             RepairOrder::create([
                'track_id' => 'RO-' . Str::upper(Str::random(8)),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle2->id,
                'advisor_id' => $advisor->id,
                'status' => 'pending',
                'odometer_reading' => 5000,
                'diagnosis_note' => 'Kiểm tra tiếng lạ ở gầm xe',
                'expected_completion_date' => now()->addDay(),
                'total_amount' => 0
            ]);
        }
    }
}
