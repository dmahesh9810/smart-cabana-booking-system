<?php

namespace App\Services;

use App\Models\Cabana;
use App\Models\Booking;
use App\Models\BookingLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function createBooking(int $userId, array $data): Booking
    {
        return DB::transaction(function () use ($userId, $data) {
            $checkIn = Carbon::parse($data['check_in'])->startOfDay();
            $checkOut = Carbon::parse($data['check_out'])->startOfDay();

            // Lock the cabana row to prevent concurrent bookings for the same cabana
            $cabana = Cabana::where('id', $data['cabana_id'])->lockForUpdate()->firstOrFail();

            if (!$cabana->is_active) {
                throw new \Exception('This cabana is not currently active.');
            }

            // Verify availability again inside the lock
            if (!$this->availabilityService->isAvailable($cabana->id, $checkIn->toDateString(), $checkOut->toDateString())) {
                throw new \Exception('The cabana is no longer available for the selected dates.');
            }

            // Calculate total amount
            $nights = $checkIn->diffInDays($checkOut);
            $totalAmount = $cabana->price_per_night * $nights;

            // Generate booking reference
            $bookingRef = $this->generateBookingReference();

            // Create booking
            $booking = Booking::create([
                'booking_ref' => $bookingRef,
                'user_id' => $userId,
                'cabana_id' => $cabana->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests_count' => $data['guests_count'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create booking log
            $this->createBookingLog($booking->id, 'booking_created');

            return $booking;
        });
    }

    private function generateBookingReference(): string
    {
        $datePart = now()->format('Ymd');
        $randomPart = strtoupper(Str::random(4));
        $reference = "CAB-{$datePart}-{$randomPart}";

        while (Booking::where('booking_ref', $reference)->exists()) {
            $randomPart = strtoupper(Str::random(4));
            $reference = "CAB-{$datePart}-{$randomPart}";
        }

        return $reference;
    }

    private function createBookingLog(int $bookingId, string $action, ?string $notes = null): void
    {
        BookingLog::create([
            'booking_id' => $bookingId,
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}
