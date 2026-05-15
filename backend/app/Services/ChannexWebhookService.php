<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\ChannexMapping;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ChannexWebhookService
{
    private AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Process the decoded webhook payload.
     */
    public function processPayload(array $payload): void
    {
        $event = $payload['event'] ?? null;
        $data = $payload['payload']['booking'] ?? [];

        if (!$event || empty($data)) {
            Log::warning('Channex Webhook: Missing event or booking data.', ['payload' => $payload]);
            return;
        }

        try {
            switch ($event) {
                case 'booking.created':
                case 'booking.updated':
                    $this->upsertBooking($data);
                    break;

                case 'booking.cancelled':
                    $this->cancelBooking($data);
                    break;

                default:
                    Log::info("Channex Webhook: Unhandled event type '{$event}'");
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Channex Webhook Error: ' . $e->getMessage(), [
                'event' => $event,
                'external_id' => $data['booking_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Creates or updates a booking based on external_id.
     */
    private function upsertBooking(array $data): void
    {
        $externalId = $data['booking_id'] ?? null;
        $roomTypeId = $data['room_type_id'] ?? null;
        $statusStr = strtolower($data['status'] ?? 'confirmed');

        if (!$externalId || !$roomTypeId) {
            throw new \Exception('Missing booking_id or room_type_id in payload.');
        }

        // Map Channex room_type_id directly using cabanas table column
        $cabana = \App\Models\Cabana::where('channex_room_type_id', $roomTypeId)->first();
        
        if (!$cabana) {
            Log::error("Channex Mapping Error: Webhook received for room_type_id {$roomTypeId}, but no local Cabana is mapped.");
            throw new \Exception("No cabana mapped for Channex room_type_id: {$roomTypeId}");
        }
        $cabanaId = $cabana->id;

        $checkIn = Carbon::parse($data['arrival_date'])->startOfDay();
        $checkOut = Carbon::parse($data['departure_date'])->startOfDay();

        // Check availability (excluding this booking if it already exists)
        $existingBooking = Booking::where('external_id', $externalId)->first();
        
        $isAvailable = $this->checkAvailabilityWithExclusion($cabanaId, $checkIn, $checkOut, $existingBooking?->id);
        
        if (!$isAvailable && $statusStr !== 'cancelled') {
            Log::error("Channex Sync Conflict: Cabana {$cabanaId} is not available.", ['external_id' => $externalId]);
            throw new \Exception('Cabana is not available for these dates.');
        }

        // Map Customer to User
        $customer = $data['customer'] ?? [];
        $email = $customer['email'] ?? "guest_{$externalId}@channex.local";
        $phone = $customer['phone'] ?? null;
        $name = trim(($customer['first_name'] ?? 'Guest') . ' ' . ($customer['last_name'] ?? ''));

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make(Str::random(16)),
                'role_id' => 2 // Assuming 2 is customer role
            ]
        );

        $totalAmount = isset($data['total_amount']) ? (float) $data['total_amount'] : 0.0;

        DB::transaction(function () use ($existingBooking, $externalId, $cabanaId, $user, $checkIn, $checkOut, $statusStr, $totalAmount) {
            $mappedStatus = $statusStr === 'cancelled' ? 'cancelled' : 'confirmed';

            if ($existingBooking) {
                // Update
                $existingBooking->update([
                    'cabana_id' => $cabanaId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $mappedStatus,
                    'total_amount' => $totalAmount,
                    // If guests count is sent by channex, update it, else keep it.
                ]);

                BookingLog::create([
                    'booking_id' => $existingBooking->id,
                    'action' => 'channex_webhook_update',
                    'notes' => "Booking updated via Channex webhook. Status: {$mappedStatus}"
                ]);
            } else {
                // Create
                $bookingRef = 'CHX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
                
                $newBooking = Booking::create([
                    'external_id' => $externalId,
                    'source' => 'channex',
                    'booking_ref' => $bookingRef,
                    'user_id' => $user->id,
                    'cabana_id' => $cabanaId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests_count' => 1, // Default, update if payload has it
                    'total_amount' => $totalAmount,
                    'status' => $mappedStatus,
                ]);

                BookingLog::create([
                    'booking_id' => $newBooking->id,
                    'action' => 'channex_webhook_create',
                    'notes' => "Booking created natively via Channex webhook."
                ]);
            }
        });

        // Clear dashboard cache
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
    }

    /**
     * Cancels an existing booking.
     */
    private function cancelBooking(array $data): void
    {
        $externalId = $data['booking_id'] ?? null;
        if (!$externalId) return;

        $booking = Booking::where('external_id', $externalId)->first();
        if ($booking && $booking->status !== 'cancelled') {
            $booking->update(['status' => 'cancelled']);
            
            BookingLog::create([
                'booking_id' => $booking->id,
                'action' => 'channex_webhook_cancel',
                'notes' => "Booking cancelled via Channex webhook."
            ]);

            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
        }
    }

    /**
     * Re-check availability, optionally excluding an existing booking ID 
     * (useful when updating an existing Channex booking's dates).
     */
    private function checkAvailabilityWithExclusion(int $cabanaId, Carbon $checkIn, Carbon $checkOut, ?int $excludeBookingId = null): bool
    {
        // 1. Check overlapping active bookings
        $hasBookings = Booking::where('cabana_id', $cabanaId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
                return $q->where('id', '!=', $excludeBookingId);
            })
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })->exists();

        if ($hasBookings) {
            return false;
        }

        // 2. Check admin blocks (from existing AvailabilityService logic)
        $hasBlocks = \App\Models\CabanaAvailability::where('cabana_id', $cabanaId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('start_date', '<', $checkOut)
                      ->where('end_date', '>', $checkIn);
            })->exists();

        if ($hasBlocks) {
            return false;
        }

        return true;
    }
}
