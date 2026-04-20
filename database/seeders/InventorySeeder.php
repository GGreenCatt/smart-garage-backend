<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;
use App\Models\Supplier;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $bosch = Supplier::where('name', 'Bosch Vietnam')->first();
        $michelin = Supplier::where('name', 'Michelin Tires')->first();
        $castrol = Supplier::where('name', 'Castrol Oil')->first();
        $denso = Supplier::where('name', 'Denso Auto Parts')->first();

        $parts = [
            [
                'name' => 'Dầu Nhớt Castrol Edge 5W-30', 'sku' => 'OIL-CAS-001', 'category' => 'Fluids',
                'purchase_price' => 350000, 'selling_price' => 480000, 'stock_quantity' => 50, 'min_stock' => 10, 'safety_stock' => 15,
                'supplier_id' => $castrol?->id
            ],
            [
                'name' => 'Lọc Dầu Bosch (Toyota/Lexus)', 'sku' => 'FLT-BSC-001', 'category' => 'Filters',
                'purchase_price' => 80000, 'selling_price' => 120000, 'stock_quantity' => 100, 'min_stock' => 20, 'safety_stock' => 30,
                'supplier_id' => $bosch?->id
            ],
            [
                'name' => 'Gạt Mưa Bosch Aerotwin 24"', 'sku' => 'WIP-BSC-024', 'category' => 'Accessories',
                'purchase_price' => 250000, 'selling_price' => 450000, 'stock_quantity' => 30, 'min_stock' => 5, 'safety_stock' => 8,
                'supplier_id' => $bosch?->id
            ],
            [
                'name' => 'Lốp Michelin Primacy 4 (205/55R16)', 'sku' => 'TIR-MIC-001', 'category' => 'Tires',
                'purchase_price' => 2100000, 'selling_price' => 2800000, 'stock_quantity' => 12, 'min_stock' => 4, 'safety_stock' => 6,
                'supplier_id' => $michelin?->id
            ],
            [
                'name' => 'Bugi Denso Iridium Power', 'sku' => 'SPK-DNS-001', 'category' => 'Engine',
                'purchase_price' => 180000, 'selling_price' => 320000, 'stock_quantity' => 60, 'min_stock' => 12, 'safety_stock' => 20,
                'supplier_id' => $denso?->id
            ],
        ];

        foreach ($parts as $p) {
            Part::firstOrCreate(['sku' => $p['sku']], $p);
        }
    }
}
