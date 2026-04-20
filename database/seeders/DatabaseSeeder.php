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
        
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $staffRole = \App\Models\Role::where('slug', 'staff')->first();
        $customerRole = \App\Models\Role::where('slug', 'customer')->first();

        // 2. Create Core Users
        // Admin
        User::firstOrCreate([
            'email' => 'admin@smartgarage.com',
        ], [
            'name' => 'Admin User',
            'role' => 'admin',
            'role_id' => $adminRole?->id,
            'password' => bcrypt('password'),
        ]);

        // Manager
        $managerRole = \App\Models\Role::where('slug', 'manager')->first();
        User::firstOrCreate([
            'email' => 'manager@smartgarage.com',
        ], [
            'name' => 'Manager User',
            'role' => 'manager',
            'role_id' => $managerRole?->id,
            'password' => bcrypt('password'),
        ]);

        // Staff (Standard)
        User::firstOrCreate([
            'email' => 'staff@smartgarage.com',
        ], [
            'name' => 'Staff Technician',
            'role' => 'staff',
            'role_id' => $staffRole?->id,
            'password' => bcrypt('password'),
        ]);

        // Staff (Technician)
        $techRole = \App\Models\Role::where('slug', 'technician')->first();
        User::firstOrCreate([
            'email' => 'tech@smartgarage.com',
        ], [
            'name' => 'Technician User',
            'role' => 'technician',
            'role_id' => $techRole?->id,
            'password' => bcrypt('password'),
        ]);

        // Customer
        User::firstOrCreate([
            'email' => 'customer@smartgarage.com',
        ], [
            'name' => 'Nguyen Van A',
            'role' => 'customer',
            'role_id' => $customerRole?->id,
            'phone' => '0909999999',
            'password' => bcrypt('password'),
        ]);

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
