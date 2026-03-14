<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    protected $signature   = 'notifications:send-reminders';
    protected $description = 'Send check-in reminder emails and SMS to guests checking in tomorrow';

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $bookings = Booking::with(['user', 'cabana', 'payment'])
            ->where('status', 'confirmed')
            ->whereDate('check_in', $tomorrow)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info("No check-ins tomorrow — nothing to send.");
            return;
        }

        $this->info("Sending reminders for {$bookings->count()} booking(s) checking in on {$tomorrow}...");

        $sent = 0;
        foreach ($bookings as $booking) {
            try {
                // Email reminder
                $this->notificationService->sendBookingReminder($booking);

                // SMS reminder
                $this->notificationService->sendSmsReminder($booking);

                $this->line("  ✓ Reminder sent for {$booking->booking_ref} ({$booking->user?->email})");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for {$booking->booking_ref}: " . $e->getMessage());
                Log::error("SendBookingReminders: Failed for {$booking->booking_ref}: " . $e->getMessage());
            }
        }

        $this->info("Done — {$sent}/{$bookings->count()} reminders sent.");
        Log::info("SendBookingReminders: {$sent}/{$bookings->count()} reminders sent for {$tomorrow}.");
    }
}
