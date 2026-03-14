<?php

namespace Database\Factories;

use App\Models\Cabana;
use Illuminate\Database\Eloquent\Factories\Factory;

class CabanaFactory extends Factory
{
    protected $model = Cabana::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Cabana',
            'description' => $this->faker->paragraph(3),
            'price_per_night' => $this->faker->numberBetween(5000, 50000),
            'max_guests' => $this->faker->numberBetween(2, 8),
            'location' => $this->faker->city() . ', Sri Lanka',
            'is_active' => true,
        ];
    }
}
