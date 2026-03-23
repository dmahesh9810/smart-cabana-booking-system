<?php

namespace App\Contracts;

use App\Models\Cabana;
use App\Models\Booking;

interface ChannelManagerInterface
{
    /**
     * Synchronize cabana availability with the external channel.
     *
     * @param Cabana $cabana
     * @param string $checkIn
     * @param string $checkOut
     * @return bool
     */
    public function syncAvailability(Cabana $cabana, string $checkIn, string $checkOut): bool;

    /**
     * Cancel an existing booking on the external channel.
     *
     * @param Booking $booking
     * @return bool
     */
    public function cancelBooking(Booking $booking): bool;
}
