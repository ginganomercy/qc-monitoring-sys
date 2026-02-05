<?php

namespace Database\Seeders;

use App\Models\DefectType;
use Illuminate\Database\Seeder;

class DefectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defectTypes = [
            // Critical defects
            ['code' => 'DEF-001', 'name' => 'Torn Fabric', 'severity' => 'critical', 'is_active' => true],
            ['code' => 'DEF-002', 'name' => 'Missing Button/Zipper', 'severity' => 'critical', 'is_active' => true],
            ['code' => 'DEF-003', 'name' => 'Open Seam', 'severity' => 'critical', 'is_active' => true],

            // High severity defects
            ['code' => 'DEF-004', 'name' => 'Incorrect Size', 'severity' => 'high', 'is_active' => true],
            ['code' => 'DEF-005', 'name' => 'Color Mismatch', 'severity' => 'high', 'is_active' => true],
            ['code' => 'DEF-006', 'name' => 'Broken Stitching', 'severity' => 'high', 'is_active' => true],
            ['code' => 'DEF-007', 'name' => 'Hole/Puncture', 'severity' => 'high', 'is_active' => true],

            // Medium severity defects
            ['code' => 'DEF-008', 'name' => 'Loose Thread', 'severity' => 'medium', 'is_active' => true],
            ['code' => 'DEF-009', 'name' => 'Puckering', 'severity' => 'medium', 'is_active' => true],
            ['code' => 'DEF-010', 'name' => 'Uneven Seam', 'severity' => 'medium', 'is_active' => true],
            ['code' => 'DEF-011', 'name' => 'Skipped Stitch', 'severity' => 'medium', 'is_active' => true],
            ['code' => 'DEF-012', 'name' => 'Crooked Label', 'severity' => 'medium', 'is_active' => true],

            // Low severity defects
            ['code' => 'DEF-013', 'name' => 'Minor Fabric Flaw', 'severity' => 'low', 'is_active' => true],
            ['code' => 'DEF-014', 'name' => 'Slight Discoloration', 'severity' => 'low', 'is_active' => true],
            ['code' => 'DEF-015', 'name' => 'Small Wrinkle', 'severity' => 'low', 'is_active' => true],
        ];

        foreach ($defectTypes as $defectType) {
            DefectType::create($defectType);
        }
    }
}
