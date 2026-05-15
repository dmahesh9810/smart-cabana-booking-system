<?php

namespace App\Console\Commands;

use App\Models\Cabana;
use App\Models\ChannexMapping;
use App\Services\ChannexService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class InitChannexMappingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channex:init-mapping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Channex mapping by fetching Smart Cabana IDs from Channex Staging and mapping to local DB';

    /**
     * Execute the console command.
     */
    public function handle(ChannexService $channexService)
    {
        $this->info('Starting Channex Mapping Initialization...');

        $propertiesResponse = $channexService->getProperties();

        if (empty($propertiesResponse['data'])) {
            $this->error('Failed to fetch properties from Channex or no properties found.');
            return Command::FAILURE;
        }

        $propertyId = null;
        foreach ($propertiesResponse['data'] as $prop) {
            if (($prop['attributes']['title'] ?? '') === 'Smart Cabana') {
                $propertyId = $prop['id'];
                break;
            }
        }

        if (!$propertyId) {
            $this->error('Property "Smart Cabana" not found in Channex Staging.');
            return Command::FAILURE;
        }

        $this->info("Found Property ID: {$propertyId}");

        $apiKey = config('services.channex.api_key');
        $baseUrl = config('services.channex.base_url', 'https://staging.channex.io/api/v1/');

        // Fetch Room Types
        $roomsResponse = Http::withHeaders(['user-api-key' => $apiKey])
            ->get($baseUrl . 'room_types', ['filter[property_id]' => $propertyId])->json();

        $roomTypeId = $roomsResponse['data'][0]['id'] ?? null;
        if (!$roomTypeId) {
            $this->error('No Room Types found for the property.');
            return Command::FAILURE;
        }
        $this->info("Found Room Type ID: {$roomTypeId}");

        // Fetch Rate Plans
        $ratesResponse = Http::withHeaders(['user-api-key' => $apiKey])
            ->get($baseUrl . 'rate_plans', ['filter[property_id]' => $propertyId])->json();

        $ratePlanId = $ratesResponse['data'][0]['id'] ?? null;
        if (!$ratePlanId) {
            $this->error('No Rate Plans found for the property.');
            return Command::FAILURE;
        }
        $this->info("Found Rate Plan ID: {$ratePlanId}");

        // Create or find dummy Cabana
        $cabana = Cabana::firstOrCreate(
            ['name' => 'Smart Cabana - Master Suite'], // Unique identifier for dummy creation
            [
                'description' => 'A luxurious test cabana mapped to Channex',
                'price_per_night' => 150.00,
                'max_guests' => 2,
                'location' => 'Beachfront',
                'is_active' => true,
            ]
        );
        $this->info("Local Cabana ID: {$cabana->id}");

        // Create or update mapping
        $mapping = ChannexMapping::updateOrCreate(
            ['cabana_id' => $cabana->id],
            [
                'property_id' => $propertyId,
                'room_type_id' => $roomTypeId,
                'rate_plan_id' => $ratePlanId,
            ]
        );

        $this->info("Successfully mapped Cabana ID {$cabana->id} to Channex!");
        $this->table(
            ['Local Cabana ID', 'Channex Property ID', 'Room Type ID', 'Rate Plan ID'],
            [
                [$mapping->cabana_id, $mapping->property_id, $mapping->room_type_id, $mapping->rate_plan_id]
            ]
        );

        return Command::SUCCESS;
    }
}
