<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ]);

        try {
            $payload = $this->paymentService->initiatePayment($request->booking_id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => $payload
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        // 6. Ensure detailed logging is in place the earliest possible moment
        Log::info('--- PAYHERE WEBHOOK ENDPOINT HIT ---', [
            'ip' => $request->ip(),
            'all_data' => $request->all()
        ]);

        try {
            $this->paymentService->handleWebhook($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed'
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function myPayments(Request $request): JsonResponse
    {
        $payments = \App\Models\Payment::whereHas('booking', function($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
        ->with('booking.cabana')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $payments->map(function ($payment) {
                return [
                    'order_id' => $payment->order_id,
                    'cabana_name' => $payment->booking->cabana->name ?? 'Unknown',
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'payment_status' => $payment->payment_status,
                    'created_at' => $payment->created_at,
                ];
            })
        ]);
    }
}
