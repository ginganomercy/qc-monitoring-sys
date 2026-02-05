<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in order: master data first, then transactional data
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            LineSeeder::class,
            DefectTypeSeeder::class,
            ComponentSeeder::class,
            DailyTargetSeeder::class,
            InspectionSeeder::class,
        ]);
    }
}
