<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\ChannexMapping;
use App\Models\Cabana;
use Carbon\Carbon;

class ChannexService
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $apiKey;

    public function __construct()
    {
        // Get config from services.php, with a fallback
        $this->baseUrl = rtrim(config('services.channex.base_url', 'https://staging.channex.io/api/v1'), '/');
        $this->apiKey = config('services.channex.api_key');
    }

    /**
     * Make an HTTP request to the Channex API.
     *
     * @param string $method GET, POST, PUT, DELETE
     * @param string $endpoint API endpoint (e.g., 'properties')
     * @param array $data Request payload/query string
     * @return array
     * @throws \Exception
     */
    private function makeRequest(string $method, string $endpoint, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'user-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->$method("{$this->baseUrl}/{$endpoint}", $data);

            if ($response->successful()) {
                Log::info("Channex API Success Response", [
                    'endpoint' => $endpoint,
                    'response' => $response->json()
                ]);
                return $response->json();
            }

            Log::error("Channex API Error", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new \Exception('Channex API request failed with status ' . $response->status() . ': ' . $response->body());

        } catch (\Exception $e) {
            Log::error("ChannexService Exception", [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Fetch all properties.
     */
    public function getProperties()
    {
        return $this->makeRequest('get', 'properties');
    }

    /**
     * Fetch room types for the property.
     */
    public function getRooms()
    {
        return $this->makeRequest('get', 'room_types');
    }

    /**
     * Fetch rate plans.
     */
    public function getRatePlans()
    {
        return $this->makeRequest('get', 'rate_plans');
    }

    /**
     * Update availability for a specific date range.
     */
    public function updateAvailability(string $propertyId, string $roomTypeId, string $dateFrom, string $dateTo, int $availability)
    {
        $payload = [
            'values' => [
                [
                    'property_id' => $propertyId,
                    'room_type_id' => $roomTypeId,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'availability' => $availability,
                ]
            ]
        ];

        Log::info("Channex Availability Update Payload", $payload);
        return $this->makeRequest('post', 'availability', $payload);
    }

    /**
     * Fetch current availability from Channex.
     */
    public function getAvailability(string $propertyId, string $roomTypeId, string $dateFrom, string $dateTo)
    {
        $query = [
            'property_id' => $propertyId,
            'room_type_id' => $roomTypeId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
        
        return $this->makeRequest('get', 'availability', $query);
    }

    /**
     * Synchronize availability for the duration of a booking.
     */
    public function syncBookingAvailability(Booking $booking)
    {
        // 1. Ensure cabana and mapping exist
        $cabana = $booking->cabana;
        if (!$cabana) return;

        $mapping = $cabana->channexMapping;
        if (!$mapping) {
            Log::info("Skipping Channex Sync: No mapping found for Cabana ID {$cabana->id}");
            return;
        }

        // 2. Calculate Base Inventory (Total Cabanas mapping to this room_type_id)
        $baseInventory = Cabana::whereHas('channexMapping', function ($q) use ($mapping) {
            $q->where('room_type_id', $mapping->room_type_id);
        })->where('is_active', true)->count();

        // If base inventory is somehow 0, fallback to 1
        $baseInventory = $baseInventory > 0 ? $baseInventory : 1;

        // 3. Iterate over the booking dates (exclusive of checkout date, as checkout day is available)
        $checkIn = Carbon::parse($booking->check_in)->startOfDay();
        $checkOut = Carbon::parse($booking->check_out)->startOfDay();
        
        $currentDate = $checkIn->copy();

        while ($currentDate->lt($checkOut)) {
            $dateStr = $currentDate->toDateString();

            // 4. Calculate overlapping confirmed bookings for this specific date
            $overlappingBookings = Booking::whereIn('cabana_id', function ($query) use ($mapping) {
                $query->select('cabana_id')
                      ->from('channex_mappings')
                      ->where('room_type_id', $mapping->room_type_id);
            })
            ->whereIn('status', ['confirmed']) // Only confirmed bookings reduce Channex availability
            ->where('check_in', '<=', $dateStr)
            ->where('check_out', '>', $dateStr)
            ->count();

            // 5. Calculate remaining availability
            $availability = max(0, $baseInventory - $overlappingBookings);

            Log::info("Channex Sync Calculation for [{$dateStr}]", [
                'base_inventory' => $baseInventory,
                'overlapping_bookings' => $overlappingBookings,
                'calculated_availability' => $availability
            ]);

            // 6. Push to Channex for this day
            try {
                $this->updateAvailability(
                    $mapping->property_id,
                    $mapping->room_type_id,
                    $dateStr,
                    $dateStr,
                    $availability
                );
            } catch (\Exception $e) {
                Log::error("Failed to sync availability for date {$dateStr}", ['error' => $e->getMessage()]);
                // Re-throw to allow Queue Job to retry
                throw $e; 
            }

            $currentDate->addDay();
        }
        
        Log::info("Channex Sync Complete for Booking ID {$booking->id}");
    }
}
