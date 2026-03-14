<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(private NotificationService $notificationService)
    {
        //
    }
    /**
     * Initiates a completely new PayHere Payment request.
     */
    public function initiatePayment(int $bookingId, int $userId): array
    {
        $booking = Booking::with('user')->where('id', $bookingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($booking->status !== 'pending') {
            throw new \Exception('Payment can only be initiated for pending bookings.');
        }

        $merchantId = (string) config('services.payhere.merchant_id');
        $merchantSecret = config('services.payhere.secret');
        $currency = 'LKR';
        
        // Generate entirely new order_id format as requested: CABANA-{booking_id}-{timestamp}
        $orderId = 'CABANA-' . $booking->id . '-' . time();
        $amount = number_format((float) $booking->total_amount, 2, '.', ''); // Strictly 2 decimals

        // Generate rigorous PayHere Hash
        $hash = strtoupper(
            md5(
                $merchantId .
                $orderId .
                $amount .
                $currency .
                strtoupper(md5($merchantSecret))
            )
        );

        // Update database payment record
        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'order_id' => $orderId,
                'amount' => $booking->total_amount,
                'currency' => $currency,
                'payment_method' => 'PayHere',
                'payment_status' => 'pending',
                'payhere_payment_id' => null
            ]
        );

        // Return exact PayHere payload matching the JS SDK
        return [
            'sandbox' => (bool) config('services.payhere.sandbox'),
            'merchant_id' => $merchantId,
            'return_url' => env('FRONTEND_URL', 'http://localhost:5173') . '/booking/success',
            'cancel_url' => env('FRONTEND_URL', 'http://localhost:5173') . '/booking/cancel',
            'notify_url' => rtrim(env('APP_URL', config('app.url')), '/') . '/api/v1/payments/payhere-webhook',
            'order_id' => $orderId,
            'items' => 'Smart Cabana Booking #' . $booking->id,
            'currency' => $currency,
            'amount' => $amount,
            'first_name' => $booking->user->name ?? 'Guest',
            'last_name' => 'User',
            'email' => $booking->user->email ?? 'no-reply@smartcabana.lk',
            'phone' => $booking->user->phone ?? '0000000000',
            'address' => 'N/A',
            'city' => 'Colombo',
            'country' => 'Sri Lanka',
            'hash' => $hash
        ];
    }

    /**
     * Strictly handles the webhook ping from PayHere servers.
     */
    public function handleWebhook(array $payload): void
    {
        Log::info('PayHere Webhook Received:', $payload);

        // Security Step: IP Whitelist Validation
        $clientIp = request()->ip();
        if (!$this->validateWebhookIp($clientIp)) {
            Log::error("PayHere Webhook: Rejected from unauthorised IP: {$clientIp}", ['payload' => $payload]);
            throw new \Exception('Webhook from unauthorised IP address');
        }

        $merchantId = $payload['merchant_id'] ?? '';
        $orderId = $payload['order_id'] ?? '';
        $payhereAmount = $payload['payhere_amount'] ?? '';
        $payhereCurrency = $payload['payhere_currency'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $md5sig = $payload['md5sig'] ?? '';
        $paymentId = $payload['payment_id'] ?? null;

        $configMerchantId = config('services.payhere.merchant_id');
        $merchantSecret = config('services.payhere.secret');

        // Validation 1: Internal Merchant ID match
        if ($merchantId !== (string)$configMerchantId) {
            Log::error('PayHere Webhook: Invalid merchant_id', ['payload' => $payload]);
            throw new \Exception('Invalid Merchant ID');
        }

        // Validation 2: MD5 Signature Generation and Match
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
            Log::error('PayHere Webhook: Signature mismatch! Potential tampering.', ['payload' => $payload, 'localHash' => $localHash]);
            throw new \Exception('Invalid Webhook Signature');
        }

        $confirmedBookingId = null;

        // DB Transaction for safety
        DB::transaction(function () use ($orderId, $payhereAmount, $paymentId, $statusCode, &$confirmedBookingId) {
            $payment = Payment::where('order_id', $orderId)->lockForUpdate()->first();
            
            if (!$payment) {
                Log::warning('PayHere Webhook: Payment record not found', ['order_id' => $orderId]);
                return;
            }

            $booking = Booking::where('id', $payment->booking_id)->lockForUpdate()->first();

            if (!$booking) {
                Log::warning('PayHere Webhook: Booking record not found', ['booking_id' => $payment->booking_id]);
                return;
            }

            // Validation 3: Check correct status code 2 = Success
            if ($statusCode == 2) {
                
                // Prevent double processing
                if ($payment->payment_status === 'paid') {
                    return;
                }

                // Strict Amount Check
                if (number_format($payment->amount, 2, '.', '') !== number_format((float)$payhereAmount, 2, '.', '')) {
                     Log::error('PayHere Webhook: Paid amount mismatch', [
                        'db_amount' => $payment->amount,
                        'received_amount' => $payhereAmount
                    ]);
                    throw new \Exception('Amount mismatch between DB and PayHere');
                }

                // Update precise records
                $payment->update([
                    'payment_status' => 'paid',
                    'payhere_payment_id' => $paymentId
                ]);

                $booking->update([
                    'status' => 'confirmed'
                ]);

                BookingLog::create([
                    'booking_id' => $booking->id,
                    'action'     => 'payment_received',
                    'notes'      => "Webhook confirmed Payment ID: {$paymentId}"
                ]);

                // Set ID so we can send notifications outside the transaction
                $confirmedBookingId = $booking->id;

            } elseif ($statusCode < 0) {
                // Handle failures or cancellations identically
                if ($payment->payment_status !== 'paid') {
                    $payment->update([
                        'payment_status' => 'failed',
                        'payhere_payment_id' => $paymentId
                    ]);
                }
            }
        });

        // Fire notifications OUTSIDE the DB transaction to avoid delaying the commit
        if ($confirmedBookingId) {
            $freshBooking = Booking::with(['user', 'cabana', 'payment'])->find($confirmedBookingId);
            if ($freshBooking) {
                // Clear dashboard cache when payment is confirmed
                \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');

                $this->notificationService->sendPaymentSuccess($freshBooking);
                
                // Dispatch queued SMS job instead of sending synchronously
                \App\Jobs\SendSmsNotificationJob::dispatch($freshBooking, 'confirmation');
            }
        }
    }

    /**
     * Validate sender IP against allowed CIDR ranges.
     */
    private function validateWebhookIp(?string $ip): bool
    {
        if (!$ip) return false;

        // Skip validation in local environment if needed, but for now we follow strict requirements
        if (app()->environment('local') && env('PAYHERE_BYPASS_IP_CHECK', false)) {
            return true;
        }

        $allowedRanges = array_filter(
            explode(',', config('services.payhere.webhook_ips', ''))
        );

        if (empty($allowedRanges)) {
            Log::warning('PayHere: No IP whitelist configured — skipping IP check');
            return true;
        }

        foreach ($allowedRanges as $range) {
            if ($this->ipInRange($ip, trim($range))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP address is within a CIDR range.
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($range, $netmask) = explode('/', $range, 2);
        
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~ $wildcardDecimal;
        
        return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
    }
}
