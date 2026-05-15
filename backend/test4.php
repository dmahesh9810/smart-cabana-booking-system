<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::find(130);
if (!$booking) { echo "Booking not found\n"; exit; }

$channexService = app(App\Services\ChannexService::class);
try {
    $channexService->syncBookingAvailability($booking);
    echo "Sync Successful.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
