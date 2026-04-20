<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access to all system modules',
                'permissions' => ['*', 'delete_vehicles'] // Wildcard for all permissions
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage staff, inventory, and vehicles',
                'permissions' => ['view_dashboard', 'manage_staff', 'manage_inventory', 'view_inventory', 'manage_vehicles', 'delete_vehicles', 'view_3d', 'edit_3d', 'manage_customers', 'manage_appointments']
            ],
            [
                'name' => 'Technician',
                'slug' => 'technician',
                'description' => 'Vehicle inspection and repair',
                'permissions' => ['view_dashboard', 'manage_vehicles', 'view_inventory', 'view_3d', 'edit_3d']
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Standard staff access',
                'permissions' => ['view_dashboard', 'manage_vehicles', 'view_inventory', 'view_3d', 'edit_3d']
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'End user access',
                'permissions' => ['view_own_vehicles', 'view_3d'] // View only, no Edit
            ]
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
