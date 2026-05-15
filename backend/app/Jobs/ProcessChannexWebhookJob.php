<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessChannexWebhookJob implements ShouldQueue
{
    use Queueable;

    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\ChannexWebhookService $webhookService): void
    {
        $webhookService->processPayload($this->payload);
    }
}
