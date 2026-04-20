<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class UpdatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Manager
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $new = [
                'view_dashboard', 'manage_settings', 'view_reports',
                'view_staff', 'manage_staff', 'access_chat',
                'manage_customers', 'view_own_vehicles', 'manage_vehicles', 'delete_vehicles', 'view_3d', 'edit_3d',
                'view_inventory', 'manage_inventory', 'manage_suppliers',
                'view_services', 'manage_services', 'manage_appointments', 'manage_promotions', 'manage_sos',
                'create_repair_orders', 'view_repair_orders', 'manage_repair_orders', 'approve_repair_orders', 'update_repair_progress', 'view_assigned_tasks',
                'manage_finance'
            ];
            $manager->update(['permissions' => $new]);
        }

        // 2. Staff
        $staff = Role::where('slug', 'staff')->first();
        if ($staff) {
            $new = [
                'view_dashboard', 'view_reports',
                'view_staff', 'access_chat',
                'manage_customers', 'view_own_vehicles', 'manage_vehicles', 'view_3d',
                'view_inventory', 'manage_inventory',
                'view_services', 'manage_appointments', 'manage_sos',
                'create_repair_orders', 'view_repair_orders', 'manage_repair_orders', 'update_repair_progress'
            ];
            $staff->update(['permissions' => $new]);
        }

        // 3. Technician (If exists)
        $tech = Role::where('slug', 'technician')->first();
        if ($tech) {
             $new = [
                 'view_dashboard',
                 'view_staff', 'access_chat',
                 'manage_vehicles', 'view_3d', 'edit_3d',
                 'view_inventory',
                 'view_repair_orders', 'update_repair_progress', 'view_assigned_tasks'
             ];
             $tech->update(['permissions' => $new]);
        }
        
        // 4. Admin (Ensure Wildcard)
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            if (!in_array('*', $admin->permissions ?? [])) {
                 $admin->update(['permissions' => ['*']]);
            }
        }
    }
}
