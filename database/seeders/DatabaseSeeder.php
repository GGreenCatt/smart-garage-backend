<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $this->call(RoleSeeder::class);

        // 2. Create Core Users
        $this->call(UserSeeder::class);

        // 3. Run other seeders in order
        $this->call([
            SupplierSeeder::class,
            ServiceSeeder::class,
            InventorySeeder::class,
            StaffSeeder::class, // Payrolls, Shifts, extra staff
            VehicleSeeder::class,
            RepairOrderSeeder::class,
            WorkShiftSeeder::class,
            SosSeeder::class,
            AppointmentSeeder::class,
            SettingSeeder::class,
            MaterialRequestSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
