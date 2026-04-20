<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Inventory seeding moved to InventorySeeder


        // 2. Get Staff User (or create one if not exists)
        $staff = \App\Models\User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Nguyễn Văn A', 'password' => bcrypt('password'), 'role' => 'staff', 'phone' => '0912345678']
        );

        // 3. Seed Payroll
        \App\Models\Payroll::updateOrCreate(
            ['user_id' => $staff->id, 'month' => now()->startOfMonth()],
            [
                'base_salary' => 15000000,
                'bonus' => 2500000,
                'deductions' => 500000,
                'total_hours' => 168,
                'overtime_hours' => 12,
                'performance_score' => 95
            ]
        );

        // 4. Seed Work Shifts (Current Week)
        $startOfWeek = now()->startOfWeek();
        $shifts = [
            ['date' => $startOfWeek->copy(), 'shift_type' => 'Morning', 'hours' => '08:00 - 17:00', 'status' => 'completed'],
            ['date' => $startOfWeek->copy()->addDays(1), 'shift_type' => 'Morning', 'hours' => '08:00 - 17:00', 'status' => 'completed'],
            ['date' => $startOfWeek->copy()->addDays(2), 'shift_type' => 'Morning', 'hours' => '08:00 - 17:00', 'status' => 'active'],
            ['date' => $startOfWeek->copy()->addDays(3), 'shift_type' => 'Afternoon', 'hours' => '13:00 - 22:00', 'status' => 'upcoming'],
            ['date' => $startOfWeek->copy()->addDays(4), 'shift_type' => 'Morning', 'hours' => '08:00 - 17:00', 'status' => 'upcoming'],
            ['date' => $startOfWeek->copy()->addDays(5), 'shift_type' => 'Off', 'hours' => 'N/A', 'status' => 'upcoming'],
        ];

        foreach ($shifts as $shift) {
            \App\Models\WorkShift::updateOrCreate(
                ['user_id' => $staff->id, 'date' => $shift['date']],
                $shift
            );
        }
    }
}
