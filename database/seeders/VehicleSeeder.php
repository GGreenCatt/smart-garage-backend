<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\User;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@smartgarage.com')->first();
        
        if (!$customer) return;

        $vehicles = [
            [
                'license_plate' => '30F-567.89', 'model' => 'VinFast Lux A2.0', 'type' => 'sedan', 
                'vin' => 'VFAST123456789', 'year' => 2021,
                'owner_name' => $customer->name, 'owner_phone' => $customer->phone
            ],
            [
                'license_plate' => '51H-999.99', 'model' => 'Ford Ranger Wildtrak', 'type' => 'truck', 
                'vin' => 'FORD123456789', 'year' => 2022,
                'owner_name' => $customer->name, 'owner_phone' => $customer->phone
            ],
            [
                'license_plate' => '29A-111.11', 'model' => 'Honda CR-V', 'type' => 'suv', 
                'vin' => 'HONDA123456789', 'year' => 2020,
                'owner_name' => $customer->name, 'owner_phone' => $customer->phone
            ]
        ];

        foreach ($vehicles as $v) {
            Vehicle::firstOrCreate(['license_plate' => $v['license_plate']], $v);
        }
    }
}
