<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cabana;
use App\Jobs\SyncCabanaIcalJob;

class SyncIcalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ical:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch iCal sync jobs for all cabanas with an iCal URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cabanas = Cabana::whereNotNull('ical_url')->where('is_active', true)->get();

        if ($cabanas->isEmpty()) {
            $this->info('No cabanas with iCal URLs found.');
            return;
        }

        foreach ($cabanas as $cabana) {
            SyncCabanaIcalJob::dispatch($cabana);
            $this->info("Dispatched iCal sync job for Cabana ID: {$cabana->id}");
        }

        $this->info('All iCal sync jobs dispatched successfully.');
    }
}
