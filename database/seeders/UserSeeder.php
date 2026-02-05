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
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create QC inspector users
        User::create([
            'name' => 'Inspector 1',
            'email' => 'inspector1@qc.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Inspector 2',
            'email' => 'inspector2@qc.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
