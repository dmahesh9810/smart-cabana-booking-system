<?php

namespace App\Services;

use App\Models\Cabana;
use App\Models\Booking;
use App\Models\BookingLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class BookingService
{
    protected $availabilityService;
    protected $notificationService;

    public function __construct(
        AvailabilityService $availabilityService,
        NotificationService $notificationService
    ) {
        $this->availabilityService = $availabilityService;
        $this->notificationService = $notificationService;
    }

    public function createBooking(int $userId, array $data): Booking
    {
        // Capture the booking returned by the transaction (not return it directly)
        $booking = DB::transaction(function () use ($userId, $data) {
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

        // Send booking created notification AFTER the transaction commits successfully
        $this->notificationService->sendBookingCreated($booking->load(['user', 'cabana']));

        // Clear dashboard cache on new booking
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');

        return $booking;
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
