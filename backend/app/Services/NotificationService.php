<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    // ── Unified Notifications ─────────────────────────────────────

    /**
     * Send notification when a booking is created.
     */
    public function sendBookingCreated(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $user = $booking->user;

        if (!$user) return;

        try {
            $user->notify(new \App\Notifications\BookingCreatedNotification($booking));
            Log::info("Booking created notification dispatched to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch booking created notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when payment is successful.
     */
    public function sendPaymentSuccess(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $user = $booking->user;

        if (!$user) return;

        try {
            $user->notify(new \App\Notifications\PaymentSuccessfulNotification($booking));
            Log::info("Payment success notification dispatched to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch payment success notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when booking is confirmed.
     */
    public function sendBookingConfirmed(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $user = $booking->user;

        if (!$user) return;

        try {
            $user->notify(new \App\Notifications\BookingConfirmedNotification($booking));
            Log::info("Booking confirmed notification dispatched to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch booking confirmed notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when booking is cancelled.
     */
    public function sendBookingCancelled(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $user = $booking->user;

        if (!$user) return;

        try {
            $user->notify(new \App\Notifications\BookingCancelledNotification($booking));
            Log::info("Booking cancelled notification dispatched to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch booking cancelled notification: " . $e->getMessage());
        }
    }

    /**
     * Send notification when booking is expired.
     */
    public function sendBookingExpired(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $user = $booking->user;

        if (!$user) return;

        try {
            $user->notify(new \App\Notifications\BookingExpiredNotification($booking));
            Log::info("Booking expired notification dispatched to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch booking expired notification: " . $e->getMessage());
        }
    }

    /**
     * Send a check-in reminder notification (called 1 day before check-in).
     * Keeping this as a placeholder or using Confirmed if appropriate.
     */
    public function sendBookingReminder(Booking $booking): void
    {
        // Placeholder for future dedicated reminder notification
        Log::info("Booking reminder trigger for {$booking->booking_ref}");
    }

    // ── SMS Notifications ──────────────────────────────────────────────
    // (Existing SMS logic kept as requested for future integration)

    public function sendSmsConfirmation(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $phone = $booking->user?->phone ?? null;
        if (!$phone) return;

        $message = "✅ Booking Confirmed! Ref: {$booking->booking_ref}. Cabana: " . ($booking->cabana?->name ?? 'N/A') . ". Thanks!";
        $this->sendSms($phone, $message, $booking->booking_ref);
    }

    public function sendSmsReminder(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $phone = $booking->user?->phone ?? null;
        if (!$phone) return;

        $message = "⏰ Reminder: Your stay at " . ($booking->cabana?->name ?? 'your cabana') . " begins TOMORROW!";
        $this->sendSms($phone, $message, $booking->booking_ref);
    }

    // ── Internals ──────────────────────────────────────────────────────

    private function sendSms(string $to, string $body, string $ref): void
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.from');

        if (!$sid || !$token || !$from) return;

        try {
            // Simplified SMS trigger logic
            Log::info("SMS dispatch attempted for {$ref} to {$to}");
        } catch (\Throwable $e) {
            Log::error("SMS dispatch error: " . $e->getMessage());
        }
    }

    private function ensureRelations(Booking $booking): void
    {
        if (!$booking->relationLoaded('user'))   $booking->load('user');
        if (!$booking->relationLoaded('cabana')) $booking->load('cabana');
        if (!$booking->relationLoaded('payment')) $booking->load('payment');
    }
}
