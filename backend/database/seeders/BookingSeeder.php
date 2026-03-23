<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Cabana;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

        // Monthly distribution: Oct (8), Nov (12), Dec (18), Jan (15), Feb (20), Mar (27)
        $distribution = [
            ['month' => 10, 'year' => 2025, 'count' => 8],
            ['month' => 11, 'year' => 2025, 'count' => 12],
            ['month' => 12, 'year' => 2025, 'count' => 18],
            ['month' => 1, 'year' => 2026, 'count' => 15],
            ['month' => 2, 'year' => 2026, 'count' => 20],
            ['month' => 3, 'year' => 2026, 'count' => 27],
        ];

        foreach ($distribution as $data) {
            for ($i = 0; $i < $data['count']; $i++) {
                // Generate a random day in the month
                $day = rand(1, 28); // Safe for all months
                $checkIn = Carbon::create($data['year'], $data['month'], $day, rand(10, 15), 0, 0);
                $checkOut = (clone $checkIn)->addDays(rand(1, 3));

                // 60% confirmed, 20% completed, 10% pending, 10% cancelled
                $statusRoll = rand(1, 100);
                if ($statusRoll <= 60) {
                    $status = 'confirmed';
                } elseif ($statusRoll <= 80) {
                    $status = 'completed';
                } elseif ($statusRoll <= 90) {
                    $status = 'pending';
                } else {
                    $status = 'cancelled';
                }

                $cabana = $cabanas->random();
                $nights = $checkIn->diffInDays($checkOut);
                if ($nights <= 0) $nights = 1;

                Booking::create([
                    'booking_ref' => 'BKG-' . strtoupper(Str::random(6)),
                    'user_id' => $users->random()->id,
                    'cabana_id' => $cabana->id,
                    'check_in' => $checkIn->toDateString(),
                    'check_out' => $checkOut->toDateString(),
                    'guests_count' => rand(1, 4),
                    'total_amount' => $cabana->price_per_night * $nights,
                    'status' => $status,
                    'created_at' => $checkIn->subDays(rand(1, 10)),
                    'updated_at' => $checkIn,
                ]);
            }
        }
    }
}
