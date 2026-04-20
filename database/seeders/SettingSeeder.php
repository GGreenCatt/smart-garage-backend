<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General
            ['key' => 'garage_name', 'value' => 'Smart Garage Saigon', 'group' => 'general'],
            ['key' => 'garage_address', 'value' => '123 Đường 3/2, Quận 10, TP.HCM', 'group' => 'general'],
            ['key' => 'garage_phone', 'value' => '0909686868', 'group' => 'general'],
            ['key' => 'garage_email', 'value' => 'contact@smartgarage.vn', 'group' => 'general'],
            
            // Finance
            ['key' => 'tax_rate', 'value' => '8', 'group' => 'finance'], // 8% VAT
            ['key' => 'currency_symbol', 'value' => '₫', 'group' => 'finance'],
            
            // System
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system'],
        ];

        foreach ($defaults as $data) {
            Setting::firstOrCreate(['key' => $data['key']], $data);
        }
    }
}
