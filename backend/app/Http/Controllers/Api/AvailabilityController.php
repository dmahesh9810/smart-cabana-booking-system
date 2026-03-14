<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Http\Requests\CheckAvailabilityRequest;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function check(CheckAvailabilityRequest $request, string $id): JsonResponse
    {
        $isAvailable = $this->availabilityService->isAvailable(
            $id,
            $request->validated('check_in'),
            $request->validated('check_out')
        );

        return response()->json([
            'success' => true,
            'available' => $isAvailable
        ]);
    }

    public function calendar(string $id): JsonResponse
    {
        $data = $this->availabilityService->getCalendar($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Return a flat list of all booked dates for a cabana (confirmed bookings only).
     * Used by the frontend availability calendar component.
     */
    public function availability(string $id): JsonResponse
    {
        $today = Carbon::today();

        $bookings = Booking::where('cabana_id', $id)
            ->where('status', 'confirmed')
            ->where('check_out', '>', $today)
            ->select('check_in', 'check_out')
            ->get();

        $bookedDates = [];

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->check_in)->startOfDay();
            $end   = Carbon::parse($booking->check_out)->startOfDay();

            // Inclusive of check-in, exclusive of check-out (allows same-day turnaround)
            while ($start->lt($end)) {
                $bookedDates[] = $start->toDateString();
                $start->addDay();
            }
        }

        // Deduplicate and sort
        $bookedDates = array_values(array_unique($bookedDates));
        sort($bookedDates);

        return response()->json([
            'cabana_id'   => (int) $id,
            'booked_dates' => $bookedDates,
        ]);
    }
}
