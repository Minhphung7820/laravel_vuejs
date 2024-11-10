<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 500 bản ghi giả cho bảng products
        Product::factory()->count(500)->create([
            'avatar' => '/storage/uploads/zg732RP3l2GtYbbCfnttEPL5B1x7H2ywTZHvT5SG.png'
        ]);
    }
}
