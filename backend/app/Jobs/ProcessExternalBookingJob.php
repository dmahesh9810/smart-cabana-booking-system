<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WebhookLog;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Cabana;
use App\Services\AvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ProcessExternalBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;
    public $webhookLogId;

    public function __construct(array $payload, int $webhookLogId)
    {
        $this->payload = $payload;
        $this->webhookLogId = $webhookLogId;
    }

    public function handle(AvailabilityService $availabilityService)
    {
        $log = WebhookLog::find($this->webhookLogId);
        
        if (!$log) {
            return;
        }

        try {
            // Idempotency Check: if this external_id already exists, skip processing.
            if (Booking::where('external_id', $this->payload['booking_id'])->exists()) {
                $log->update(['status' => 'success', 'error_message' => 'Skipped: Duplicate webhook']);
                return;
            }

            DB::transaction(function () use ($availabilityService) {
                // Find and lock the cabana to prevent double booking race condition
                $cabana = Cabana::where('external_id', $this->payload['room_id'])
                                ->lockForUpdate()
                                ->first();

                if (!$cabana) {
                    throw new Exception("Cabana mapping failed. No internal cabana matches external room_id: {$this->payload['room_id']}");
                }

                if (!$cabana->is_active) {
                    throw new Exception("Cabana is inactive. Cannot map external booking to: {$this->payload['room_id']}");
                }

                $checkIn = Carbon::parse($this->payload['checkin'])->startOfDay();
                $checkOut = Carbon::parse($this->payload['checkout'])->startOfDay();

                // Validate availability inside the lock
                if (!$availabilityService->isAvailable($cabana->id, $checkIn->toDateString(), $checkOut->toDateString())) {
                    throw new Exception("Double Booking overlap. Cabana ID {$cabana->id} is already booked for these dates.");
                }

                // Generate booking reference
                $bookingRef = "EXT-CAB-" . now()->format('Ymd') . "-" . strtoupper(Str::random(4));
                while (Booking::where('booking_ref', $bookingRef)->exists()) {
                    $bookingRef = "EXT-CAB-" . now()->format('Ymd') . "-" . strtoupper(Str::random(4));
                }

                // Calculate total amount (could be customized or taken from external payload if available)
                $nights = $checkIn->diffInDays($checkOut);
                $totalAmount = $cabana->price_per_night * $nights;

                // Create the external booking
                $booking = Booking::create([
                    'booking_ref' => $bookingRef,
                    'user_id' => null, // Optional requirement as agreed with the user
                    'cabana_id' => $cabana->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests_count' => $cabana->max_guests, // default to max guests since payload doesn't provide it
                    'total_amount' => $totalAmount,
                    'status' => 'confirmed', // Assuming external integration confirms instantly
                    'source' => 'booking.com',
                    'external_id' => $this->payload['booking_id']
                ]);

                // Create a booking log
                BookingLog::create([
                    'booking_id' => $booking->id,
                    'action' => 'booking_created',
                    'notes' => 'External sync via Booking.com webhook.',
                ]);
            });

            // Mark webhook as success
            $log->update(['status' => 'success']);

        } catch (Exception $e) {
            // Mark webhook as failed and save error message
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            ]);

            // Re-throw if we want standard job retry/failing mechanisms
            // throw $e; 
        }
    }
}
