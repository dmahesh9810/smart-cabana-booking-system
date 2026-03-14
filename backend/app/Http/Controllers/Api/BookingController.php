<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

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
        return $this->successResponse(BookingResource::collection($bookings), 'Bookings retrieved successfully');
    }

    /**
     * Store a newly created booking.
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking($request->user()->id, $request->validated());

            return $this->successResponse(
                new BookingResource($booking->load('cabana')),
                'Booking created successfully',
                201
            );
        }
        catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
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

        return $this->successResponse(new BookingResource($booking), 'Booking details retrieved successfully');
    }
}
