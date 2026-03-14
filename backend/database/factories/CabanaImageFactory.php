<?php

namespace Database\Factories;

use App\Models\CabanaImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CabanaImageFactory extends Factory
{
    protected $model = CabanaImage::class;

    public function definition(): array
    {
        $images = [
            'https://images.unsplash.com/photo-1501117716987-c8e1ecb2100c',
            'https://images.unsplash.com/photo-1505691938895-1758d7feb511',
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267',
            'https://images.unsplash.com/photo-1540518614846-7eded433c457',
            'https://images.unsplash.com/photo-1571896349842-33c89424de2d',
            'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2',
            'https://images.unsplash.com/photo-1439066615861-d1af74d74000',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
            'https://images.unsplash.com/photo-1519046904884-53103b34b206',
            'https://images.unsplash.com/photo-1506953823976-52e1bdc0149a',
        ];

        return [
            'image_path' => $this->faker->randomElement($images),
            'is_primary' => false,
        ];
    }
}
