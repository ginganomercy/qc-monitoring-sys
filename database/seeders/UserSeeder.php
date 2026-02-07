<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin QC',
            'email' => 'admin@qc.com',
            'password' => Hash::make('tegal*2020'),
            'email_verified_at' => now(),
        ]);

        // Create QC inspector user
        User::create([
            'name' => 'Alisa',
            'email' => 'alisa2891@qc.com',
            'password' => Hash::make('tegal*2020'),
            'email_verified_at' => now(),
        ]);
    }
}
