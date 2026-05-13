<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * ⚠️ IMPORTANT: Set SEED_ADMIN_PASSWORD in .env before running.
     *    Default fallback is intentionally weak to force you to change it.
     */
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD', 'ChangeMe!2026');

        // Create admin user
        User::create([
            'name' => 'Admin QC',
            'email' => 'admin@qc.com',
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Create QC inspector user
        User::create([
            'name' => 'Alisa',
            'email' => 'alisa2891@qc.com',
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
    }
}
