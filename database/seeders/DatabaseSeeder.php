<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            LicenseKeySeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
            PromoCodeSeeder::class,
        ]);
    }
}
