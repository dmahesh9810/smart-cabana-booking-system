<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::find(130);
$mapping = $booking->cabana->channexMapping;

$checkIn = \Carbon\Carbon::parse($booking->check_in)->startOfDay();
$checkOut = \Carbon\Carbon::parse($booking->check_out)->startOfDay();

$currentDate = $checkIn->copy();
while ($currentDate->lt($checkOut)) {
    $dateStr = $currentDate->toDateString();

    $overlappingBookings = App\Models\Booking::whereIn('cabana_id', function ($query) use ($mapping) {
        $query->select('cabana_id')
              ->from('channex_mappings')
              ->where('room_type_id', $mapping->room_type_id);
    })
    ->whereIn('status', ['confirmed'])
    ->where('check_in', '<=', $dateStr)
    ->where('check_out', '>', $dateStr)
    ->count();

    echo "Date: {$dateStr} - Overlapping: {$overlappingBookings}\n";
    $currentDate->addDay();
}
