<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Services\ChannexService;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncChannexAvailabilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Booking
     */
    public $booking;

    /**
     * Retry times
     */
    public $tries = 3;

    /**
     * Backoff wait times between retries
     */
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     *
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Execute the job.
     *
     * @param ChannexService $channexService
     */
    public function handle(ChannexService $channexService)
    {
        try {
            Log::info("Starting Channex Sync for Booking ID: {$this->booking->id}");
            
            $channexService->syncBookingAvailability($this->booking);
            
            Log::info("Successfully synced Channex availability for Booking ID: {$this->booking->id}");
        } catch (Exception $e) {
            Log::error("Failed to sync Channex availability for Booking ID {$this->booking->id}: " . $e->getMessage());
            throw $e; // Throw exception to trigger retries
        }
    }
}
