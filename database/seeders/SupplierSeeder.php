<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Bosch Vietnam', 'contact_person' => 'Mr. Hung', 'phone' => '0901234567', 'email' => 'sales@bosch.vn', 'address' => 'District 1, HCMC'],
            ['name' => 'Michelin Tires', 'contact_person' => 'Ms. Lan', 'phone' => '0909876543', 'email' => 'contact@michelin.vn', 'address' => 'District 7, HCMC'],
            ['name' => 'Castrol Oil', 'contact_person' => 'Mr. Tuan', 'phone' => '0912345678', 'email' => 'distributor@castrol.vn', 'address' => 'Thu Duc City, HCMC'],
            ['name' => 'Denso Auto Parts', 'contact_person' => 'Mr. Minh', 'phone' => '0988776655', 'email' => 'support@denso.com.vn', 'address' => 'Tan Binh, HCMC'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['email' => $s['email']], $s);
        }
    }
}
