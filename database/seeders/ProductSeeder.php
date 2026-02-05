<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['style_number' => 'T-SHIRT-001', 'description' => 'Basic Cotton T-Shirt - White', 'is_active' => true],
            ['style_number' => 'T-SHIRT-002', 'description' => 'Premium Polo Shirt - Navy', 'is_active' => true],
            ['style_number' => 'PANTS-001', 'description' => 'Denim Jeans - Blue Wash', 'is_active' => true],
            ['style_number' => 'PANTS-002', 'description' => 'Cargo Pants - Khaki', 'is_active' => true],
            ['style_number' => 'JACKET-001', 'description' => 'Bomber Jacket - Black', 'is_active' => true],
            ['style_number' => 'SHIRT-001', 'description' => 'Formal Dress Shirt - White', 'is_active' => true],
            ['style_number' => 'HOODIE-001', 'description' => 'Pullover Hoodie - Grey', 'is_active' => true],
            ['style_number' => 'DRESS-001', 'description' => 'Summer Dress - Floral Print', 'is_active' => true],
            ['style_number' => 'SWEATER-001', 'description' => 'Knit Sweater - Burgundy', 'is_active' => true],
            ['style_number' => 'SHORTS-001', 'description' => 'Athletic Shorts - Black', 'is_active' => true],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
