<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('role', 'staff')->first();
        if(!$staff) return;

        $notifications = [
            [
                'type' => 'material_request_status',
                'data' => [
                    'title' => 'Yêu cầu được duyệt',
                    'message' => 'Yêu cầu "Nhớt Castrol" đã được duyệt.',
                    'url' => route('staff.requests.index'),
                    'vehicle' => 'Admin System'
                ]
            ],
            [
                'type' => 'assign_job',
                'data' => [
                    'title' => 'Công việc mới',
                    'message' => 'Bạn được phân công sửa xe 30A-123.45',
                    'url' => route('staff.dashboard'),
                    'vehicle' => '30A-123.45'
                ]
            ]
        ];

        foreach($notifications as $n) {
            Notification::create([
                'id' => Str::uuid(),
                'type' => $n['type'],
                'notifiable_type' => get_class($staff),
                'notifiable_id' => $staff->id,
                'data' => $n['data'],
                'read_at' => null,
                'created_at' => Carbon::now()
            ]);
        }
    }
}
