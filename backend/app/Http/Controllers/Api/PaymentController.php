<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponse;

    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        try {
            $payload = $this->paymentService->initiatePayment($request->booking_id, $request->user()->id);

            return $this->successResponse($payload, 'Payment initiated successfully');
        }
        catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        Log::info('--- PAYHERE WEBHOOK ENDPOINT HIT ---', [
            'ip' => $request->ip(),
            'all_data' => $request->all()
        ]);

        try {
            $this->paymentService->handleWebhook($request->all());

            return $this->successResponse(null, 'Webhook processed successfully');
        }
        catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
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

        $data = $payments->map(function ($payment) {
            return [
                'order_id' => $payment->order_id,
                'cabana_name' => $payment->booking->cabana->name ?? 'Unknown',
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payment_status' => $payment->payment_status,
                'created_at' => $payment->created_at,
            ];
        });

        return $this->successResponse($data, 'Payments retrieved successfully');
    }
}
