<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::all();

        foreach ($bookings as $booking) {
            // Determine payment status based on booking status
            $paymentStatus = in_array($booking->status, ['confirmed', 'completed']) ? 'paid' : 'pending';
            
            // Random payment method
            $paymentMethod = fake()->randomElement(['PayHere', 'Card', 'Bank Transfer']);

            Payment::create([
                'booking_id' => $booking->id,
                'order_id' => 'ORD-' . strtoupper(fake()->unique()->bothify('###??#')),
                'amount' => $booking->total_amount,
                'currency' => 'LKR',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payhere_payment_id' => $paymentStatus === 'paid' ? fake()->unique()->numerify('1200#######') : null,
                'created_at' => $booking->created_at, // Use booking's created_at for trend alignment
                'updated_at' => $booking->created_at,
            ]);
        }
    }
}
