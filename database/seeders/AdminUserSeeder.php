<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // 2. Standard Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Store Manager',
                'password' => Hash::make('password'),
                'status' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }
    }
}
