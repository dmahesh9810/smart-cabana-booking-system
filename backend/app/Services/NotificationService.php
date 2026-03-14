<?php

namespace App\Services;

use App\Mail\BookingConfirmationMail;
use App\Mail\BookingReminderMail;
use App\Mail\PaymentReceiptMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    // ── Email Notifications ────────────────────────────────────────────

    /**
     * Send a booking confirmation email to the guest.
     */
    public function sendBookingConfirmation(Booking $booking): void
    {
        $this->ensureRelations($booking);

        $email = $booking->user?->email;
        if (!$email) {
            Log::warning("NotificationService: No email for booking {$booking->booking_ref}");
            return;
        }

        try {
            Mail::to($email)->send(new BookingConfirmationMail($booking));
            Log::info("Booking confirmation sent to {$email} for {$booking->booking_ref}");
        } catch (\Throwable $e) {
            Log::error("Failed to send booking confirmation for {$booking->booking_ref}: " . $e->getMessage());
        }
    }

    /**
     * Send a payment receipt email after successful payment.
     */
    public function sendPaymentReceipt(Booking $booking): void
    {
        $this->ensureRelations($booking);

        $email = $booking->user?->email;
        if (!$email) {
            Log::warning("NotificationService: No email for booking {$booking->booking_ref}");
            return;
        }

        try {
            Mail::to($email)->send(new PaymentReceiptMail($booking));
            Log::info("Payment receipt sent to {$email} for {$booking->booking_ref}");
        } catch (\Throwable $e) {
            Log::error("Failed to send payment receipt for {$booking->booking_ref}: " . $e->getMessage());
        }
    }

    /**
     * Send a check-in reminder email (called 1 day before check-in).
     */
    public function sendBookingReminder(Booking $booking): void
    {
        $this->ensureRelations($booking);

        $email = $booking->user?->email;
        if (!$email) {
            Log::warning("NotificationService: No email for booking {$booking->booking_ref}");
            return;
        }

        try {
            Mail::to($email)->send(new BookingReminderMail($booking));
            Log::info("Booking reminder sent to {$email} for {$booking->booking_ref}");
        } catch (\Throwable $e) {
            Log::error("Failed to send booking reminder for {$booking->booking_ref}: " . $e->getMessage());
        }
    }

    // ── SMS Notifications ──────────────────────────────────────────────

    /**
     * Send an SMS booking confirmation via Twilio.
     * Requires TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM in .env.
     */
    public function sendSmsConfirmation(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $phone = $booking->user?->phone ?? null;

        if (!$phone) {
            Log::info("NotificationService: No phone number for booking {$booking->booking_ref} — SMS skipped.");
            return;
        }

        $message = "✅ Booking Confirmed!\n"
            . "Ref: {$booking->booking_ref}\n"
            . "Cabana: " . ($booking->cabana?->name ?? 'N/A') . "\n"
            . "Check-in: {$booking->check_in}\n"
            . "Thanks for booking with Smart Cabana!";

        $this->sendSms($phone, $message, $booking->booking_ref);
    }

    /**
     * Send an SMS reminder (1 day before check-in).
     */
    public function sendSmsReminder(Booking $booking): void
    {
        $this->ensureRelations($booking);
        $phone = $booking->user?->phone ?? null;

        if (!$phone) {
            Log::info("NotificationService: No phone number for booking {$booking->booking_ref} — SMS skipped.");
            return;
        }

        $message = "⏰ Reminder: Your stay at "
            . ($booking->cabana?->name ?? 'your cabana')
            . " begins TOMORROW ({$booking->check_in})!\n"
            . "Ref: {$booking->booking_ref}\n"
            . "We look forward to welcoming you. 🌴";

        $this->sendSms($phone, $message, $booking->booking_ref);
    }

    // ── Internals ──────────────────────────────────────────────────────

    /**
     * Send an SMS via Twilio REST API.
     */
    private function sendSms(string $to, string $body, string $ref): void
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::warning("NotificationService: Twilio credentials not configured — SMS skipped for {$ref}.");
            return;
        }

        try {
            $url  = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
            $data = ['From' => $from, 'To' => $to, 'Body' => $body];

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($data),
                CURLOPT_USERPWD        => "{$sid}:{$token}",
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info("SMS sent to {$to} for booking {$ref}");
            } else {
                Log::error("Twilio SMS failed ({$httpCode}) for booking {$ref}: {$response}");
            }
        } catch (\Throwable $e) {
            Log::error("SMS exception for booking {$ref}: " . $e->getMessage());
        }
    }

    /**
     * Eager-load relationships if not already loaded to avoid N+1 in loops.
     */
    private function ensureRelations(Booking $booking): void
    {
        if (!$booking->relationLoaded('user'))   $booking->load('user');
        if (!$booking->relationLoaded('cabana')) $booking->load('cabana');
        if (!$booking->relationLoaded('payment')) $booking->load('payment');
    }
}
