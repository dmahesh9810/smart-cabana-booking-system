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
        Cabana::factory()
            ->count(15)
            ->create()
            ->each(function ($cabana) {
                // Create 3-5 images for each cabana
                \App\Models\CabanaImage::factory()
                    ->count(rand(3, 5))
                    ->create([
                        'cabana_id' => $cabana->id,
                    ]);
                
                // Set the first image as primary
                $cabana->images()->first()->update(['is_primary' => true]);
            });
    }
}
