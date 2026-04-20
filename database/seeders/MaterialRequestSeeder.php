<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaterialRequest;
use App\Models\User;
use Carbon\Carbon;

class MaterialRequestSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('role', 'staff')->first();
        if(!$staff) return;

        $requests = [
            [
                'part_name' => 'Nhớt Castrol Power1 10W-40',
                'quantity' => 20,
                'reason' => 'Kho sắp hết, cần nhập gấp cho đợt bảo dưỡng cuối tuần',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(2)
            ],
            [
                'part_name' => 'Lốp Michelin City Grip 2',
                'quantity' => 10,
                'reason' => 'Khách đặt trước',
                'status' => 'approved',
                'admin_note' => 'Đã duyệt, hàng về ngày mai',
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'part_name' => 'Bugi NGK Iridium',
                'quantity' => 100,
                'reason' => 'Nhập tồn kho',
                'status' => 'rejected',
                'admin_note' => 'Số lượng quá lớn, kiểm tra lại tồn kho hiện tại',
                'created_at' => Carbon::now()->subDays(2)
            ]
        ];

        foreach($requests as $req) {
            MaterialRequest::create(array_merge(['staff_id' => $staff->id], $req));
        }
    }
}
