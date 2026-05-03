<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $staffRole = Role::where('slug', 'staff')->first();
        $techRole = Role::where('slug', 'technician')->first();
        $customerRole = Role::where('slug', 'customer')->first();

        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@smartgarage.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'role_id' => $adminRole?->id,
                'phone' => '0901000001',
                'status' => 'active',
            ],
            [
                'name' => 'Quản Lý',
                'email' => 'manager@smartgarage.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'role_id' => $managerRole?->id,
                'phone' => '0901000002',
                'status' => 'active',
            ],
            [
                'name' => 'Nhân Viên',
                'email' => 'staff@smartgarage.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'role_id' => $staffRole?->id,
                'phone' => '0901000003',
                'status' => 'active',
            ],
            [
                'name' => 'Kỹ Thuật Viên',
                'email' => 'tech@smartgarage.com',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'role_id' => $techRole?->id,
                'phone' => '0901000004',
                'status' => 'active',
            ],
            [
                'name' => 'Khách Hàng',
                'email' => 'customer@smartgarage.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'role_id' => $customerRole?->id,
                'phone' => '0901000005',
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
