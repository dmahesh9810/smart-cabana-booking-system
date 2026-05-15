<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPaymentResource;
use App\Services\SystemActivityService;
use Illuminate\Http\Request;

use App\Traits\ApiResponse;

class AdminPaymentController extends Controller
{
    use ApiResponse;

    private \App\Services\SystemActivityService $dashboardService;

    public function __construct(SystemActivityService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display a paginated list of payments for admins
     */
    public function index()
    {
        $payments = $this->dashboardService->getPayments();
        return $this->successResponse(AdminPaymentResource::collection($payments), 'Payments retrieved successfully');
    }

    /**
     * Manually mark a payment as paid and confirm the booking.
     */
    public function markAsPaid($id, \App\Services\PaymentService $paymentService)
    {
        $payment = \App\Models\Payment::findOrFail($id);

        if ($payment->payment_status === 'paid') {
            return $this->errorResponse('Payment is already marked as paid.', 400);
        }

        $booking = \App\Models\Booking::find($payment->booking_id);
        if (!$booking) {
            return $this->errorResponse('Associated booking not found.', 404);
        }

        // 1. Update Payment Status
        $payment->update([
            'payment_status' => 'paid',
            'payhere_payment_id' => 'MANUAL-CONFIRM-' . time()
        ]);

        // 2. Update Booking Status
        $booking->update([
            'status' => 'confirmed'
        ]);

        // 3. Log the action
        \App\Models\BookingLog::create([
            'booking_id' => $booking->id,
            'action'     => 'payment_received',
            'notes'      => 'Manual payment confirmation by Admin'
        ]);

        // 4. Clear Dashboard Cache
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');

        $freshBooking = \App\Models\Booking::with(['user', 'cabana', 'payment'])->find($booking->id);

        // 5. Fire Notifications and Sync
        if ($freshBooking) {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendPaymentSuccess($freshBooking);

            \App\Jobs\SendSmsNotificationJob::dispatch($freshBooking, 'confirmation');
            \App\Jobs\SyncChannexAvailabilityJob::dispatch($freshBooking);
        }

        return $this->successResponse(new AdminPaymentResource($payment), 'Payment marked as paid and booking confirmed successfully.');
    }
}
