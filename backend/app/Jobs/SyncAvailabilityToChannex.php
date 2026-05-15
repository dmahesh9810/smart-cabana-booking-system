<?php

namespace App\Jobs;

use App\Models\Cabana;
use App\Services\ChannexService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAvailabilityToChannex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cabanaId;
    public $date;
    public $isAvailable;

    public $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Retina delay: 30s, 60s, 120s
    }

    /**
     * Create a new job instance.
     */
    public function __construct($cabanaId, $date, $isAvailable)
    {
        $this->cabanaId = $cabanaId;
        $this->date = $date;
        $this->isAvailable = $isAvailable;
    }

    /**
     * Execute the job.
     */
    public function handle(ChannexService $channexService): void
    {
        $cabana = Cabana::with('channexMapping')->find($this->cabanaId);

        if (!$cabana || !$cabana->channexMapping) {
            Log::info("Skip Channex sync: No mapping found for cabana_id {$this->cabanaId}");
            return;
        }

        $mapping = $cabana->channexMapping;
        $availabilityCount = $this->isAvailable ? 1 : 0;

        $response = $channexService->pushAvailability(
            $mapping->property_id,
            $mapping->room_type_id,
            $mapping->rate_plan_id,
            $this->date,
            $availabilityCount
        );

        Log::info("Channex sync success for cabana_id {$this->cabanaId} on date {$this->date}");
    }
}
