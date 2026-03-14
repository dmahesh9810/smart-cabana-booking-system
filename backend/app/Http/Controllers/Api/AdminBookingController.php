<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminBookingResource;
use App\Models\Booking;
use App\Services\SystemActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    private SystemActivityService $dashboardService;

    public function __construct(SystemActivityService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Return a paginated list of all bookings with user, cabana, and payment.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $status  = $request->input('status');

        $query = Booking::with(['user', 'cabana', 'payment'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => AdminBookingResource::collection($bookings),
            'meta'    => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    /**
     * Return full details of a single booking.
     */
    public function show(string $id): JsonResponse
    {
        $booking = Booking::with(['user', 'cabana', 'payment'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => new AdminBookingResource($booking),
        ]);
    }

    /**
     * Update the booking status (pending / confirmed / cancelled).
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $booking = Booking::with(['user', 'cabana', 'payment'])->findOrFail($id);
        $booking->status = $request->input('status');
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated to ' . $booking->status,
            'data'    => new AdminBookingResource($booking),
        ]);
    }

    /**
     * Hard-delete a booking record (admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully.',
        ]);
    }
}
