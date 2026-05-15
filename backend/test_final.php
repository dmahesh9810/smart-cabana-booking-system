<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::find(130);
if (!$booking) { echo "Booking not found\n"; exit; }

$channexService = app(App\Services\ChannexService::class);

echo "Starting Sync for Booking ID 130...\n";
try {
    $channexService->syncBookingAvailability($booking);
    echo "Sync method completed.\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\nLatest 5 log entries from laravel.log:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -15);
    foreach ($lastLines as $line) {
        if (strpos($line, 'Channex') !== false) {
            echo trim($line) . "\n";
        }
    }
}
