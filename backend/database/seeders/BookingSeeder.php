<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Role;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customerUser = User::whereHas('role', function ($q) {
            $q->where('name', 'customer');
        })->first();

        $cabana = Cabana::first();

        if ($customerUser && $cabana) {
            Booking::firstOrCreate(
            ['booking_ref' => 'BKG-12345'],
            [
                'user_id' => $customerUser->id,
                'cabana_id' => $cabana->id,
                'check_in' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'check_out' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'guests_count' => 2,
                'total_amount' => $cabana->price_per_night * 2,
                'status' => 'confirmed'
            ]
            );
        }
    }
}
