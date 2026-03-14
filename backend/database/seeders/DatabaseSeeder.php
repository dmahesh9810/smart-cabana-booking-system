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
            RoleSeeder::class,
            AmenitySeeder::class,
            UserSeeder::class,
            CabanaSeeder::class,
            BookingSeeder::class,
            // ReviewSeeder is handled within BookingSeeder now
        ]);
    }
}
