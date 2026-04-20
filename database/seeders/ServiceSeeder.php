<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Thay Dầu Máy (Fully Synthetic)', 'code' => 'SVC-OIL-01', 'category' => 'Maintenance', 'base_price' => 500000, 'description' => 'Thay dầu tổng hợp toàn phần Castrol/Mobil1'],
            ['name' => 'Thay Lọc Dầu', 'code' => 'SVC-FLT-01', 'category' => 'Maintenance', 'base_price' => 150000, 'description' => 'Thay lọc dầu chính hãng'],
            ['name' => 'Vệ Sinh Khoang Máy', 'code' => 'SVC-CLN-01', 'category' => 'Cleaning', 'base_price' => 800000, 'description' => 'Vệ sinh và dưỡng khoang máy'],
            ['name' => 'Cân Bằng Động Lốp', 'code' => 'SVC-TIR-01', 'category' => 'Repair', 'base_price' => 200000, 'description' => 'Cân bằng động 4 bánh'],
            ['name' => 'Kiểm Tra Tổng Quát (20 hạng mục)', 'code' => 'SVC-CHK-01', 'category' => 'Inspection', 'base_price' => 300000, 'description' => 'Kiểm tra an toàn tổng quát xe'],
            ['name' => 'Sơn Dặm Cản Trước', 'code' => 'SVC-PNT-01', 'category' => 'Bodywork', 'base_price' => 1200000, 'description' => 'Sơn dặm vết xước cản trước'],
        ];

        foreach ($services as $s) {
            Service::firstOrCreate(['code' => $s['code']], $s);
        }
    }
}
