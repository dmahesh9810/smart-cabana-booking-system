<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Cabana;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Services\IcalService;
use App\Services\AvailabilityService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class SyncCabanaIcalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cabana;

    public function __construct(Cabana $cabana)
    {
        $this->cabana = $cabana;
    }

    public function handle(IcalService $icalService, AvailabilityService $availabilityService)
    {
        Log::info("Starting iCal sync for Cabana {$this->cabana->id}");

        $events = $icalService->fetchAndParse($this->cabana->ical_url);

        if (empty($events)) {
            Log::info("No events found or fetch failed for Cabana {$this->cabana->id} from {$this->cabana->ical_url}");
            return;
        }

        $insertedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($events as $event) {
            try {
                // Idempotency: Check if booking exists based on iCal UID
                if (Booking::where('external_id', $event['uid'])->exists()) {
                    $skippedCount++;
                    continue; // Skip duplicate
                }

                DB::transaction(function () use ($event, $availabilityService) {
                    // Lock cabana
                    $cabana = Cabana::where('id', $this->cabana->id)->lockForUpdate()->first();

                    $checkIn = Carbon::parse($event['start'])->startOfDay();
                    $checkOut = Carbon::parse($event['end'])->startOfDay();

                    // Skip past events
                    if ($checkOut->isPast()) {
                        return; // returning from transaction closure safely skips
                    }

                    // Check availability
                    if (!$availabilityService->isAvailable($cabana->id, $checkIn->toDateString(), $checkOut->toDateString())) {
                        throw new Exception("Conflict: Dates already blocked for Cabana {$cabana->id}");
                    }

                    // Insert Booking
                    $bookingRef = "ICAL-CAB-" . now()->format('Ymd') . "-" . strtoupper(Str::random(4));
                    while (Booking::where('booking_ref', $bookingRef)->exists()) {
                        $bookingRef = "ICAL-CAB-" . now()->format('Ymd') . "-" . strtoupper(Str::random(4));
                    }

                    $nights = max(1, $checkIn->diffInDays($checkOut));
                    $totalAmount = $cabana->price_per_night * $nights;

                    $booking = Booking::create([
                        'booking_ref' => $bookingRef,
                        'user_id' => null,
                        'cabana_id' => $cabana->id,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'guests_count' => $cabana->max_guests,
                        'total_amount' => $totalAmount,
                        'status' => 'confirmed',
                        'source' => 'ical',
                        'external_id' => $event['uid'],
                    ]);

                    BookingLog::create([
                        'booking_id' => $booking->id,
                        'action' => 'booking_created',
                        'notes' => 'Imported via iCal sync.',
                    ]);
                });

                if (!Booking::where('external_id', $event['uid'])->exists()) {
                     // The transaction successfully skipped due to past date
                     $skippedCount++;
                } else {
                     $insertedCount++;
                }

            } catch (Exception $e) {
                // Log conflict or error, but do not throw to allow remaining events to process
                Log::warning("iCal Sync Event Error (Cabana {$this->cabana->id}, UID {$event['uid']}): " . $e->getMessage());
                $errorCount++;
            }
        }

        Log::info("Finished iCal sync for Cabana {$this->cabana->id} | Inserted: {$insertedCount} | Skipped: {$skippedCount} | Errors: {$errorCount}");
    }
}
