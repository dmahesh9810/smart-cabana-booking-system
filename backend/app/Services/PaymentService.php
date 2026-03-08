<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function initiatePayment(int $bookingId, int $userId): array
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($booking->status !== 'pending') {
            throw new \Exception('Payment can only be initiated for pending bookings.');
        }

        // Create or update payment record
        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $booking->total_amount,
                'payment_method' => 'PayHere',
                'status' => 'pending',
            ]
        );

        $merchantId = config('payhere.merchant_id');
        $merchantSecret = config('payhere.merchant_secret');
        $currency = config('payhere.currency');
        $orderId = $booking->booking_ref;
        $amount = number_format($booking->total_amount, 2, '.', '');

        // Hash pattern: strtoupper(md5(merchant_id . order_id . amount . currency . strtoupper(md5(merchant_secret))))
        $hash = strtoupper(
            md5(
                $merchantId .
                $orderId .
                $amount .
                $currency .
                strtoupper(md5($merchantSecret))
            )
        );

        return [
            'merchant_id' => $merchantId,
            'return_url' => url('/api/v1/payments/return'),
            'cancel_url' => url('/api/v1/payments/cancel'),
            'notify_url' => url('/api/v1/payments/payhere-webhook'),
            'order_id' => $orderId,
            'items' => 'Cabana Booking',
            'currency' => $currency,
            'amount' => $amount,
            'hash' => $hash,
            'first_name' => $booking->user->name ?? 'Guest',
            'last_name' => '',
            'email' => $booking->user->email ?? '',
            'phone' => $booking->user->phone ?? '',
            'address' => '',
            'city' => '',
            'country' => 'Sri Lanka',
        ];
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('PayHere Webhook payload:', $payload);

        $merchantId = $payload['merchant_id'] ?? '';
        $orderId = $payload['order_id'] ?? '';
        $payhereAmount = $payload['payhere_amount'] ?? '';
        $payhereCurrency = $payload['payhere_currency'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $md5sig = $payload['md5sig'] ?? '';
        $paymentId = $payload['payment_id'] ?? null;

        $configMerchantId = config('payhere.merchant_id');
        $merchantSecret = config('payhere.merchant_secret');

        // Check merchant ID matches config
        if ($merchantId !== (string)$configMerchantId) {
            Log::warning('PayHere Webhook: Invalid merchant_id', ['payload' => $payload]);
            throw new \Exception('Invalid Merchant ID');
        }

        $localHash = strtoupper(
            md5(
                $merchantId .
                $orderId .
                $payhereAmount .
                $payhereCurrency .
                $statusCode .
                strtoupper(md5($merchantSecret))
            )
        );

        if ($localHash !== strtoupper($md5sig)) {
            Log::warning('PayHere Webhook: Signature mismatch', ['payload' => $payload]);
            throw new \Exception('Invalid signature');
        }

        if ($statusCode == 2) {
            DB::transaction(function () use ($orderId, $payhereAmount, $paymentId) {
                // Find booking by reference (order_id)
                $booking = Booking::where('booking_ref', $orderId)->lockForUpdate()->firstOrFail();

                // Validate the amount strictly
                if (number_format($booking->total_amount, 2, '.', '') !== number_format((float)$payhereAmount, 2, '.', '')) {
                    Log::error('PayHere Webhook: Amount mismatch', [
                        'expected' => $booking->total_amount,
                        'received' => $payhereAmount
                    ]);
                    throw new \Exception('Amount mismatch');
                }

                $payment = Payment::where('booking_id', $booking->id)->lockForUpdate()->firstOrFail();

                if ($payment->status === 'successful') {
                    // Prevent duplicate processing
                    return;
                }

                $payment->update([
                    'status' => 'successful',
                    'transaction_id' => $paymentId
                ]);

                $booking->update([
                    'status' => 'confirmed'
                ]);

                BookingLog::create([
                    'booking_id' => $booking->id,
                    'action' => 'payment_received',
                    'notes' => "Payment ID: {$paymentId}"
                ]);
            });
        } elseif ($statusCode < 0) {
            // Negative status code implies failure or cancellation
            DB::transaction(function () use ($orderId, $paymentId) {
                $booking = Booking::where('booking_ref', $orderId)->first();
                if ($booking) {
                    $payment = Payment::where('booking_id', $booking->id)->first();
                    if ($payment && $payment->status !== 'successful') {
                        $payment->update([
                            'status' => 'failed',
                            'transaction_id' => $paymentId
                        ]);
                    }
                }
            });
        }
    }
}
