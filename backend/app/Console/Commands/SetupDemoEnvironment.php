<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cabana;
use App\Models\ChannexMapping;
use App\Services\ChannexService;
use Illuminate\Support\Facades\Log;

class SetupDemoEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the database environment for Channex demo synchronization';

    /**
     * Execute the console command.
     */
    public function handle(ChannexService $channexService)
    {
        $this->info('Starting Demo Environment Setup...');

        try {
            $this->info('Fetching Channex Properties...');
            $propertiesResponse = $channexService->getProperties();
            $properties = $propertiesResponse['data'] ?? $propertiesResponse;
            
            if (empty($properties)) {
                $this->error('No properties found in Channex. Please ensure your API key is correct.');
                return;
            }

            // Assume the first property is the Smart Cabana Resort
            $propertyId = $properties[0]['id'] ?? $properties[0]['property']['id'] ?? null;
            if (!$propertyId) {
                $this->error('Could not find property ID in response.');
                dump($properties);
                return;
            }
            $this->info("Using Property ID: {$propertyId}");

            $this->info('Fetching Channex Room Types...');
            $roomsResponse = $channexService->getRooms();
            $rooms = $roomsResponse['data'] ?? $roomsResponse;

            if (empty($rooms)) {
                $this->error('No rooms found in Channex property.');
                return;
            }

            // Assume the first room is Deluxe Cabana
            $roomId = $rooms[0]['id'] ?? $rooms[0]['room_type']['id'] ?? null;
            if (!$roomId) {
                $this->error('Could not find room ID in response.');
                dump($rooms);
                return;
            }
            $this->info("Using Room Type ID: {$roomId}");

            $this->info('Fetching Channex Rate Plans...');
            $ratesResponse = $channexService->getRatePlans();
            $rates = $ratesResponse['data'] ?? $ratesResponse;

            if (empty($rates)) {
                $this->error('No rate plans found in Channex property.');
                return;
            }

            $ratePlanId = $rates[0]['id'] ?? $rates[0]['rate_plan']['id'] ?? null;
            if (!$ratePlanId) {
                $this->error('Could not find rate plan ID in response.');
                dump($rates);
                return;
            }
            $this->info("Using Rate Plan ID: {$ratePlanId}");

            // Setup Local Database
            $this->info('Ensuring Deluxe Cabanas exist locally (Inventory = 5)...');
            
            for ($i = 1; $i <= 5; $i++) {
                $cabana = Cabana::firstOrCreate(
                    ['name' => "Deluxe Cabana Unit {$i}"],
                    [
                        'description' => 'A luxurious cabana for your perfect getaway.',
                        'price_per_night' => 15000.00,
                        'max_guests' => 2,
                        'location' => 'Beachfront',
                        'is_active' => true,
                    ]
                );

                // Ensure mapping exists
                ChannexMapping::updateOrCreate(
                    ['cabana_id' => $cabana->id],
                    [
                        'property_id' => $propertyId,
                        'room_type_id' => $roomId,
                        'rate_plan_id' => $ratePlanId,
                    ]
                );
            }

            $this->info('Demo Setup Completed Successfully!');
            $this->info("You now have 5 Cabanas mapped to Channex Room Type {$roomId}");

        } catch (\Exception $e) {
            $this->error('An error occurred during setup: ' . $e->getMessage());
            Log::error($e);
        }
    }
}
