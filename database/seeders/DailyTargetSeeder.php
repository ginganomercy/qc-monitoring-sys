<?php

namespace Database\Seeders;

use App\Models\DailyTarget;
use App\Models\Line;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = Line::all();

        // Create targets for last 7 days and next 7 days
        for ($i = -7; $i <= 7; $i++) {
            $date = Carbon::today()->addDays($i);

            foreach ($lines as $line) {
                DailyTarget::create([
                    'line_id' => $line->id,
                    'target_date' => $date,
                    'target_quantity' => rand(150, 300), // Random target between 150-300 units
                ]);
            }
        }
    }
}
