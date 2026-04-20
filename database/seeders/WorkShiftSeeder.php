<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkShift;
use App\Models\User;
use Carbon\Carbon;

class WorkShiftSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have some staff
        $staffMembers = User::where('role', 'staff')->get();

        if ($staffMembers->isEmpty()) {
            $this->command->info('No staff members found. Skipping WorkShift seeding.');
            return;
        }

        // Seed for current week + next week
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->addWeek()->endOfWeek();

        foreach ($staffMembers as $staff) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Randomize shift
                $rand = rand(1, 10);
                
                if ($rand <= 4) {
                    $type = 'Morning';
                    $hours = '08:00 - 12:00';
                } elseif ($rand <= 8) {
                    $type = 'Afternoon';
                    $hours = '13:00 - 17:00';
                } else {
                    $type = 'Off';
                    $hours = null;
                }

                // Status
                if ($currentDate->isPast() && !$currentDate->isToday()) {
                    $status = 'completed';
                } elseif ($currentDate->isToday()) {
                    $status = 'active';
                } else {
                    $status = 'upcoming';
                }

                WorkShift::firstOrCreate(
                    [
                        'user_id' => $staff->id,
                        'date' => $currentDate->format('Y-m-d'),
                    ],
                    [
                        'shift_type' => $type,
                        'hours' => $hours,
                        'status' => $status
                    ]
                );

                $currentDate->addDay();
            }
        }
    }
}
