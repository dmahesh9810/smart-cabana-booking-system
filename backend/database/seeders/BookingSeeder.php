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
        $users = User::all(); // All generated users from UserSeeder are valid for bookings
        $cabanas = Cabana::where('is_active', true)->get();

        if ($users->isEmpty() || $cabanas->isEmpty()) {
            return; // Seed cabanas and users first
        }

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $cabana = $cabanas->random();
            
            // Random check-in date between 30 days ago and today
            $startOffset = rand() % 30; // 0 to 29
            $stayDuration = (rand() % 5) + 1; // 1 to 5 nights

            $checkIn = Carbon::now()->subDays($startOffset);
            $checkOut = (clone $checkIn)->addDays($stayDuration);

            Booking::create([
                'booking_ref' => 'BKG-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'cabana_id' => $cabana->id,
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'guests_count' => rand(1, $cabana->max_guests ?: 2),
                'total_amount' => $cabana->price_per_night * $stayDuration,
                'status' => 'confirmed',
            ]);
        }
    }
}
