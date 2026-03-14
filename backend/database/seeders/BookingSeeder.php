<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Cabana;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereHas('role', function($q) { $q->where('name', 'customer'); })->get();
        $cabanas = Cabana::all();

        if ($users->isEmpty() || $cabanas->isEmpty()) {
            return;
        }

        Booking::factory()
            ->count(40)
            ->create([
                'user_id' => fn() => $users->random()->id,
                'cabana_id' => fn() => $cabanas->random()->id,
            ])
            ->each(function ($booking) {
                // Calculate correct amount
                $checkIn = Carbon::parse($booking->check_in);
                $checkOut = Carbon::parse($booking->check_out);
                $nights = $checkIn->diffInDays($checkOut);
                if ($nights <= 0) $nights = 1;
                
                $booking->total_amount = $booking->cabana->price_per_night * $nights;
                $booking->save();

                // Create payment
                $status = ($booking->status === 'confirmed' || $booking->status === 'completed') ? 'paid' : 'pending';
                \App\Models\Payment::factory()->create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_amount,
                    'payment_status' => $status,
                ]);

                // Create review for completed bookings
                if ($booking->status === 'completed') {
                    \App\Models\Review::factory()->create([
                        'booking_id' => $booking->id,
                        'user_id' => $booking->user_id,
                        'cabana_id' => $booking->cabana_id,
                    ]);
                }
            });
    }
}
