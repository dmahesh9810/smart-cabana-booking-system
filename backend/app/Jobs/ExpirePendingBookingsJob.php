<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpirePendingBookingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $expiryThreshold = Carbon::now()->subMinutes(15);

        $expiredBookings = Booking::where('status', 'pending')
            ->where('created_at', '<=', $expiryThreshold)
            ->with('user')
            ->get();

        if ($expiredBookings->isEmpty()) {
            return;
        }

        foreach ($expiredBookings as $booking) {
            try {
                $booking->update(['status' => 'cancelled']);

                BookingLog::create([
                    'booking_id' => $booking->id,
                    'action' => 'booking_cancelled',
                    'notes' => 'Auto-cancelled due to payment timeout (15 mins).'
                ]);

                $notificationService->sendBookingCancelled($booking);

                Log::info("Booking {$booking->booking_ref} auto-cancelled due to timeout.");
            } catch (\Exception $e) {
                Log::error("Failed to expire booking {$booking->id}: " . $e->getMessage());
            }
        }
    }
}
