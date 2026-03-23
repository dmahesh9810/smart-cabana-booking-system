<?php

namespace App\Services\Integrations;

use App\Contracts\ChannelManagerInterface;
use App\Models\Cabana;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class BookingComService implements ChannelManagerInterface
{
    public function syncAvailability(Cabana $cabana, string $checkIn, string $checkOut): bool
    {
        // For now, simulate network API payload construction
        $payload = [
            'room_id' => $cabana->external_id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'blocked',
        ];

        Log::info('Simulating Booking.com Availability Sync', $payload);

        // Simulated HTTP call would go here
        // Http::post('https://api.booking.com/...', $payload);

        return true;
    }

    public function cancelBooking(Booking $booking): bool
    {
        $payload = [
            'booking_id' => $booking->external_id,
            'status' => 'cancelled',
        ];

        Log::info('Simulating Booking.com Booking Cancellation', $payload);

        return true;
    }
}
