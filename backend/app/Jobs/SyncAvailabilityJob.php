<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Contracts\ChannelManagerInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncAvailabilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $action; // 'created', 'cancelled'

    /**
     * Retry times
     */
    public $tries = 3;

    /**
     * Backoff wait times between retries
     */
    public $backoff = [10, 30, 60];

    public function __construct(int $bookingId, string $action)
    {
        $this->bookingId = $bookingId;
        $this->action = $action;
    }

    public function handle(ChannelManagerInterface $channelManager)
    {
        $booking = Booking::with('cabana')->find($this->bookingId);

        if (!$booking) {
            return;
        }

        // We only want to sync outwards if it was a LOCAL booking to prevent infinite loops
        if ($booking->source !== 'local' && $booking->source !== null) {
            Log::info("Skipping outbound sync for external booking {$booking->id}");
            return;
        }

        try {
            if ($this->action === 'created') {
                $checkIn = $booking->check_in->toDateString();
                $checkOut = $booking->check_out->toDateString();
                $channelManager->syncAvailability($booking->cabana, $checkIn, $checkOut);
            } elseif ($this->action === 'cancelled') {
                $channelManager->cancelBooking($booking);
            }
        } catch (Exception $e) {
            Log::error("Failed to sync booking {$this->bookingId} to channel manager: " . $e->getMessage());
            throw $e; // Triggers retry logic
        }
    }
}
