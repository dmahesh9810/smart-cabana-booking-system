<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessExternalBookingJob;
use App\Models\WebhookLog;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Booking.com (Simulated)
     */
    public function handleBookingComWebhook(Request $request)
    {
        // 1. Log the incoming webhook regardless of validity
        $webhookLog = WebhookLog::create([
            'source' => 'booking.com',
            'event_type' => 'booking_created', // default for now, can be extracted from payload
            'payload' => $request->all(),
            'status' => 'pending'
        ]);

        // 2. Validate payload format
        $validatedData = $request->validate([
            'booking_id' => 'required|string',
            'room_id' => 'required|string',
            'checkin' => 'required|date|date_format:Y-m-d',
            'checkout' => 'required|date|date_format:Y-m-d|after:checkin',
        ]);

        // 3. Dispatch job to process the booking asynchronously
        ProcessExternalBookingJob::dispatch($validatedData, $webhookLog->id);

        // 4. Return early 200 OK
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received and processing started.',
        ], 200);
    }
}
