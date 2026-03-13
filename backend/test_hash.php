<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::where('status', 'pending')->first();
if (!$booking) {
    echo "No pending booking found.\n";
    exit;
}

$service = new App\Services\PaymentService();
$payload = $service->initiatePayment($booking->id, $booking->user_id);

echo "--- EXAMPLE PAYLOAD ---\n";
echo json_encode($payload, JSON_PRETTY_PRINT)."\n";
echo "--- EXAMPLE HASH OUTPUT ---\n";
echo $payload['hash']."\n";

