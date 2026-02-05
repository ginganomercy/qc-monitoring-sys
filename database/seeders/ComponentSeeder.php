<?php

namespace Database\Seeders;

use App\Models\Component;
use Illuminate\Database\Seeder;

class ComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            ['name' => 'Collar', 'is_active' => true],
            ['name' => 'Sleeve', 'is_active' => true],
            ['name' => 'Cuff', 'is_active' => true],
            ['name' => 'Button', 'is_active' => true],
            ['name' => 'Zipper', 'is_active' => true],
            ['name' => 'Pocket', 'is_active' => true],
            ['name' => 'Hem', 'is_active' => true],
            ['name' => 'Seam', 'is_active' => true],
            ['name' => 'Label', 'is_active' => true],
            ['name' => 'Hood', 'is_active' => true],
            ['name' => 'Waistband', 'is_active' => true],
            ['name' => 'Shoulder', 'is_active' => true],
        ];

        foreach ($components as $component) {
            Component::create($component);
        }
    }
}
