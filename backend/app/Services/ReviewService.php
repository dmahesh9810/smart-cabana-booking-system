<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * Validate if the user is eligible to review the booking.
     *
     * @param int $bookingId
     * @param int $userId
     * @return \App\Models\Booking
     * @throws ValidationException
     */
    public function validateBookingEligibility(int $bookingId, int $userId): Booking
    {
        $booking = Booking::with('review')->findOrFail($bookingId);

        if ($booking->user_id !== $userId) {
            throw ValidationException::withMessages([
                'booking_id' => 'You can only review your own bookings.'
            ]);
        }

        if ($booking->status !== 'completed') {
            throw ValidationException::withMessages([
                'booking_id' => 'You can only review completed bookings.'
            ]);
        }

        if ($booking->review) {
            throw ValidationException::withMessages([
                'booking_id' => 'You have already reviewed this booking.'
            ]);
        }

        return $booking;
    }

    /**
     * Create a new review for the booking.
     *
     * @param \App\Models\Booking $booking
     * @param array $data
     * @return \App\Models\Review
     */
    public function createReview(Booking $booking, array $data): Review
    {
        return Review::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'cabana_id' => $booking->cabana_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => true,
        ]);
    }
}
