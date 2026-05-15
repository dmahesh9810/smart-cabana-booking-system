<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChannexService;
use Illuminate\Http\JsonResponse;

class ChannexController extends Controller
{
    /**
     * @var ChannexService
     */
    protected $channexService;

    /**
     * ChannexController constructor.
     *
     * @param ChannexService $channexService
     */
    public function __construct(ChannexService $channexService)
    {
        $this->channexService = $channexService;
    }

    /**
     * Test the connection to Channex API.
     *
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            // Ping properties to test if the API key is valid
            $this->channexService->getProperties();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Connection to Channex API is successful.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to Channex API.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve properties from Channex.
     *
     * @return JsonResponse
     */
    public function properties(): JsonResponse
    {
        try {
            $data = $this->channexService->getProperties();

            return response()->json([
                'status' => 'success',
                'data' => $data['data'] ?? $data // Extract 'data' wrapper if exists
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve properties.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve rooms from Channex.
     *
     * @return JsonResponse
     */
    public function rooms(): JsonResponse
    {
        try {
            $data = $this->channexService->getRooms();

            return response()->json([
                'status' => 'success',
                'data' => $data['data'] ?? $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve rooms.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve rate plans from Channex.
     *
     * @return JsonResponse
     */
    public function ratePlans(): JsonResponse
    {
        try {
            $data = $this->channexService->getRatePlans();

            return response()->json([
                'status' => 'success',
                'data' => $data['data'] ?? $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve rate plans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get availability for a property and room type.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function getAvailability(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|string',
            'room_type_id' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        try {
            $data = $this->channexService->getAvailability(
                $request->property_id,
                $request->room_type_id,
                $request->date_from,
                $request->date_to
            );

            return response()->json([
                'status' => 'success',
                'data' => $data['data'] ?? $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve availability.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually sync availability for all upcoming confirmed bookings.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function syncAllAvailability(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            // Find all confirmed bookings from today onwards
            $bookings = \App\Models\Booking::whereIn('status', ['confirmed'])
                ->where('check_out', '>', now())
                ->get();

            $dispatchedCount = 0;
            foreach ($bookings as $booking) {
                \App\Jobs\SyncChannexAvailabilityJob::dispatch($booking);
                $dispatchedCount++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Successfully dispatched $dispatchedCount booking(s) for Channex synchronization.",
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to dispatch synchronization jobs.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
