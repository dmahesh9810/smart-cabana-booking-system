<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Cabana;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkIn = Carbon::now()->addDays($this->faker->numberBetween(-30, 30));
        $checkOut = (clone $checkIn)->addDays($this->faker->numberBetween(1, 7));

        return [
            'booking_ref' => 'BKG-' . strtoupper($this->faker->bothify('??###?')),
            'user_id' => User::factory(),
            'cabana_id' => Cabana::factory(),
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'guests_count' => $this->faker->numberBetween(1, 4),
            'total_amount' => 0, // Should be calculated based on nights and cabana price
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled', 'completed']),
        ];
    }
}
