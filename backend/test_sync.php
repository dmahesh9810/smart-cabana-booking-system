<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mapping = App\Models\ChannexMapping::first();
if (!$mapping) {
    echo "No mapping found.\n";
    exit;
}
echo "Room Type ID: " . $mapping->room_type_id . "\n";

$baseInventory = App\Models\Cabana::whereHas('channexMapping', function ($q) use ($mapping) {
    $q->where('room_type_id', $mapping->room_type_id);
})->where('is_active', true)->count();

echo "Base Inventory: " . $baseInventory . "\n";

$dateStr = '2026-05-14'; // or today
$overlappingBookings = App\Models\Booking::whereIn('cabana_id', function ($query) use ($mapping) {
    $query->select('cabana_id')
          ->from('channex_mappings')
          ->where('room_type_id', $mapping->room_type_id);
})
->whereIn('status', ['confirmed'])
->where('check_in', '<=', $dateStr)
->where('check_out', '>', $dateStr)
->count();

echo "Overlapping Bookings on {$dateStr}: " . $overlappingBookings . "\n";
echo "Availability: " . max(0, $baseInventory - $overlappingBookings) . "\n";
