<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Amenity::insert([
            ['name' => 'WiFi', 'icon' => 'wifi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Air Conditioning', 'icon' => 'ac_unit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Private Pool', 'icon' => 'pool', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TV', 'icon' => 'tv', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitchen', 'icon' => 'kitchen', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ocean View', 'icon' => 'water', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
