<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::find(130);
if (!$booking) { echo 'Booking not found.'; exit; }
$cabana = $booking->cabana;
$mapping = $cabana->channexMapping;
echo 'Cabana ID: ' . $cabana->id . "\n";
echo 'Room Type ID: ' . ($mapping ? $mapping->room_type_id : 'None') . "\n";

if ($mapping) {
    $baseInventory = App\Models\Cabana::whereHas('channexMapping', function ($q) use ($mapping) {
        $q->where('room_type_id', $mapping->room_type_id);
    })->where('is_active', true)->count();
    echo 'Base Inventory for this room type: ' . $baseInventory . "\n";
}
