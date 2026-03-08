<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

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
}
