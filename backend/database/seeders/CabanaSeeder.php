<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabana;
use App\Models\CabanaImage;
use App\Models\CabanaAvailability;
use App\Models\Amenity;

class CabanaSeeder extends Seeder
{
    public function run(): void
    {
        $cabana1 = Cabana::firstOrCreate(
        ['name' => 'Ocean View Cabana'],
        [
            'description' => 'A beautiful cabana with an ocean view.',
            'price_per_night' => 150.00,
            'max_guests' => 4,
            'location' => 'Beachfront',
            'is_active' => true
        ]
        );

        CabanaImage::firstOrCreate(
        ['cabana_id' => $cabana1->id, 'image_path' => 'cabanas/ocean1.jpg'],
        ['is_primary' => true]
        );

        CabanaAvailability::firstOrCreate(
        [
            'cabana_id' => $cabana1->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(6)->format('Y-m-d')
        ],
        ['reason' => 'Maintenance']
        );

        $amenity = Amenity::first();
        if ($amenity && !$cabana1->amenities->contains($amenity->id)) {
            $cabana1->amenities()->attach($amenity->id);
        }
    }
}
