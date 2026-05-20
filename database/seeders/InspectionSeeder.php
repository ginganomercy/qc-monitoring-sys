<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\DefectType;
use App\Models\Inspection;
use App\Models\Line;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $lines = Line::all();
        $defectTypes = DefectType::all();
        $components = Component::all();
        $inspector = User::first(); // Use first admin user

        // Create inspections for last 7 days
        for ($i = -7; $i < 0; $i++) {
            $date = Carbon::today()->addDays($i);

            // Create 30-50 inspections per day
            $inspectionsPerDay = rand(30, 50);

            for ($j = 0; $j < $inspectionsPerDay; $j++) {
                $status = rand(1, 100) <= 85 ? 'pass' : 'reject'; // 85% pass rate

                $inspectionData = [
                    'inspection_date' => $date,
                    'product_id' => $products->random()->id,
                    'line_id' => $lines->random()->id,
                    'status' => $status,
                    'user_id' => $inspector->id,
                ];

                // If rejected, add defect details
                if ($status === 'reject') {
                    $inspectionData['defect_type_id'] = $defectTypes->random()->id;
                    $inspectionData['component_id'] = $components->random()->id;
                    $inspectionData['notes'] = 'Quality issue detected during inspection.';
                }

                Inspection::create($inspectionData);
            }
        }

        // Create inspections for today (ongoing)
        $inspectionsToday = rand(15, 25);
        for ($j = 0; $j < $inspectionsToday; $j++) {
            $status = rand(1, 100) <= 85 ? 'pass' : 'reject';

            $inspectionData = [
                'inspection_date' => Carbon::today(),
                'product_id' => $products->random()->id,
                'line_id' => $lines->random()->id,
                'status' => $status,
                'user_id' => $inspector->id,
            ];

            if ($status === 'reject') {
                $inspectionData['defect_type_id'] = $defectTypes->random()->id;
                $inspectionData['component_id'] = $components->random()->id;
                $inspectionData['notes'] = 'Quality issue detected during inspection.';
            }

            Inspection::create($inspectionData);
        }
    }
}
