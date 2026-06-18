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

        // Create Leader user
        User::updateOrCreate(
            ['email' => 'alisa2891@qc.com'],
            [
                'name' => 'Alisa (Leader)',
                'password' => Hash::make($password),
                'role' => User::ROLE_LEADER,
                'email_verified_at' => now(),
            ]
        );
    }
}
