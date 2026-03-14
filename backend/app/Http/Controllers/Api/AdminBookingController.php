<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminBookingResource;
use App\Models\Booking;
use App\Services\SystemActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class AdminBookingController extends Controller
{
    use ApiResponse;
    private SystemActivityService $dashboardService;
    private \App\Services\CommissionService $commissionService;
    private \App\Services\NotificationService $notificationService;

    public function __construct(
        SystemActivityService $dashboardService,
        \App\Services\CommissionService $commissionService,
        \App\Services\NotificationService $notificationService
    ) {
        $this->dashboardService = $dashboardService;
        $this->commissionService = $commissionService;
        $this->notificationService = $notificationService;
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

        return $this->successResponse(AdminBookingResource::collection($bookings), 'Bookings retrieved successfully', 200, [
            'current_page' => $bookings->currentPage(),
            'last_page'    => $bookings->lastPage(),
            'per_page'     => $bookings->perPage(),
            'total'        => $bookings->total(),
        ]);
    }

    /**
     * Return full details of a single booking.
     */
    public function show(string $id): JsonResponse
    {
        $booking = Booking::with(['user', 'cabana', 'payment'])->findOrFail($id);

        return $this->successResponse(new AdminBookingResource($booking), 'Booking details retrieved successfully');
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
        $oldStatus = $booking->status;
        $booking->status = $request->input('status');
        $booking->save();

        // Clear dashboard cache
        Cache::forget('admin_dashboard_stats');

        // Record commission if transition to completed
        if ($booking->status === 'completed' && $oldStatus !== 'completed') {
            $this->commissionService->recordCommission($booking);
        }

        // Trigger Emails
        if ($booking->status === 'confirmed' && $oldStatus !== 'confirmed') {
            $this->notificationService->sendBookingConfirmed($booking);
        } elseif ($booking->status === 'cancelled' && $oldStatus !== 'cancelled') {
            $this->notificationService->sendBookingCancelled($booking);
        }

        $message = 'Booking status updated to ' . $booking->status;
        return $this->successResponse(new AdminBookingResource($booking), $message);
    }

    /**
     * Hard-delete a booking record (admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->forceDelete();

        // Clear dashboard cache
        Cache::forget('admin_dashboard_stats');

        return $this->successResponse(null, 'Booking deleted successfully');
    }
}
