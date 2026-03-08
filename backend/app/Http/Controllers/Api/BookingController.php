<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display a listing of bookings for the authenticated user.
     */
    public function index(Request $request)
    {
        $bookings = Booking::with('cabana')->where('user_id', $request->user()->id)->latest()->get();
        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking($request->user()->id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => new BookingResource($booking->load('cabana'))
            ], 201);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified booking if it belongs to the user.
     */
    public function show(Request $request, string $id)
    {
        $booking = Booking::with('cabana')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return new BookingResource($booking);
    }
}
