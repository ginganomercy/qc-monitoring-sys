<?php

namespace Database\Seeders;

use App\Models\Line;
use Illuminate\Database\Seeder;

class LineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = [
            ['code' => 'LINE-A', 'name' => 'Production Line A - T-Shirts', 'is_active' => true],
            ['code' => 'LINE-B', 'name' => 'Production Line B - Bottoms', 'is_active' => true],
            ['code' => 'LINE-C', 'name' => 'Production Line C - Outerwear', 'is_active' => true],
            ['code' => 'LINE-D', 'name' => 'Production Line D - Formal Wear', 'is_active' => true],
            ['code' => 'LINE-E', 'name' => 'Production Line E - Knitwear', 'is_active' => true],
        ];

        foreach ($lines as $line) {
            Line::create($line);
        }
    }
}
