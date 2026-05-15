<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Jobs\ProcessChannexWebhookJob;
use Illuminate\Support\Facades\Log;

class ChannexWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Channex.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Security: Validate incoming token
        $expectedToken = config('services.channex.webhook_token');
        $providedToken = $request->header('X-Channex-Webhook-Token');

        if (!$expectedToken || $providedToken !== $expectedToken) {
            Log::warning('Channex Webhook: Unauthorized access attempt.', [
                'ip' => $request->ip(),
                'provided_token' => $providedToken
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        // 2. Validate basic structure to prevent complete garbage processing
        if (!isset($payload['event']) || !isset($payload['payload'])) {
            return response()->json(['error' => 'Bad Request: Invalid Payload Structure'], 400);
        }

        // 3. Dispatch to Queue for asynchronous processing
        ProcessChannexWebhookJob::dispatch($payload);

        // 4. Return success quickly to acknowledge receipt
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received and queued for processing.'
        ], 200);
    }
}
