<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabana;
use Illuminate\Support\Facades\DB;

class CabanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cabanas = [
            [
                'name' => 'Ocean View Cabana',
                'description' => 'A beautiful cabana with an uninterrupted view of the ocean. Perfect for couples.',
                'price_per_night' => 12000,
                'max_guests' => 2,
                'location' => 'Beachfront',
                'is_active' => true,
            ],
            [
                'name' => 'Sunset Retreat',
                'description' => 'Enjoy the most breathtaking sunset views from the comfort of your private cabana.',
                'price_per_night' => 15000,
                'max_guests' => 2,
                'location' => 'West Coast Beach',
                'is_active' => true,
            ],
            [
                'name' => 'Tropical Jungle Hideaway',
                'description' => 'Surrounded by lush greenery, this cabana offers ultimate privacy and peace.',
                'price_per_night' => 10000,
                'max_guests' => 4,
                'location' => 'Jungle Edge',
                'is_active' => true,
            ],
            [
                'name' => 'Luxury Poolside Cabana',
                'description' => 'Steps away from the main pool, offering quick access to refreshments and swimming.',
                'price_per_night' => 18000,
                'max_guests' => 4,
                'location' => 'Poolside',
                'is_active' => true,
            ],
            [
                'name' => 'Family Beach Villa',
                'description' => 'Spacious cabana perfect for a family vacation right on the sandy shores.',
                'price_per_night' => 25000,
                'max_guests' => 6,
                'location' => 'Beachfront',
                'is_active' => true,
            ],
            [
                'name' => 'Breeze Eco-Cabana',
                'description' => 'Constructed with sustainable materials, offering a rustic yet comfortable experience.',
                'price_per_night' => 8000,
                'max_guests' => 2,
                'location' => 'Garden',
                'is_active' => true,
            ],
            [
                'name' => 'Cliffside Panoramic Suite',
                'description' => 'Perched atop a cliff, this premium suite features panoramic sea and sky views.',
                'price_per_night' => 30000,
                'max_guests' => 2,
                'location' => 'Cliff Top',
                'is_active' => true,
            ],
            [
                'name' => 'Serenity Garden Pod',
                'description' => 'A cozy pod nestled in our botanical garden, ideal for a quiet retreat.',
                'price_per_night' => 9500,
                'max_guests' => 2,
                'location' => 'Garden',
                'is_active' => true,
            ],
        ];

        foreach ($cabanas as $cabanaData) {
            $cabana = Cabana::firstOrCreate(
                ['name' => $cabanaData['name']],
                [
                    'description' => $cabanaData['description'],
                    'price_per_night' => $cabanaData['price_per_night'],
                    'max_guests' => $cabanaData['max_guests'],
                    'location' => $cabanaData['location'],
                    'is_active' => $cabanaData['is_active'],
                ]
            );

            // Add the image to the cabana_images table if it doesn't exist
            DB::table('cabana_images')->updateOrInsert(
                [
                    'cabana_id' => $cabana->id,
                    'image_path' => "https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=",
                ],
                [
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
