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
            RoleSeeder::class, // Preserving existing RoleSeeder just in case
            UserSeeder::class,
            CabanaSeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class
        ]);
    }
}
