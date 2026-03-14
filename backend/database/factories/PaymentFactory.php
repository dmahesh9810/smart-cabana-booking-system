<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'order_id' => 'ORD-' . $this->faker->unique()->numberBetween(100000, 999999),
            'amount' => 0, // Should match booking total_amount
            'currency' => 'LKR',
            'payment_method' => $this->faker->randomElement(['PayHere', 'Card', 'Bank Transfer']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payhere_payment_id' => $this->faker->uuid(),
        ];
    }
}
