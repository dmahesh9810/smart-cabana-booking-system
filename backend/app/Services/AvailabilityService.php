<?php

namespace App\Services;

use App\Models\Cabana;
use App\Models\Booking;
use App\Models\CabanaAvailability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Check if a cabana is available for the given date range.
     * Uses overlap logic: Start1 < End2 AND End1 > Start2
     */
    public function isAvailable(int $cabanaId, string $checkIn, string $checkOut): bool
    {
        $cabana = Cabana::find($cabanaId);

        if (!$cabana || !$cabana->is_active) {
            return false;
        }

        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();

        // 1. Check overlapping bookings (assuming 'pending' and 'confirmed' block dates)
        // Adjust these statuses based on the actual status enum if they differ.
        $hasBookings = Booking::where('cabana_id', $cabanaId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
            // start < checkOut AND end > checkIn
            $query->where('check_in', '<', $checkOutDate)
                ->where('check_out', '>', $checkInDate);
        })
            ->exists();

        if ($hasBookings) {
            return false;
        }

        // 2. Check admin blocked dates
        $hasBlocks = CabanaAvailability::where('cabana_id', $cabanaId)
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
            $query->where('start_date', '<', $checkOutDate)
                ->where('end_date', '>', $checkInDate);
        })
            ->exists();

        if ($hasBlocks) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve calendar block and booking dates for frontend UI.
     */
    public function getCalendar(int $cabanaId): array
    {
        $today = Carbon::today();

        $bookings = Booking::where('cabana_id', $cabanaId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_out', '>', $today)
            ->select('id', 'check_in', 'check_out')
            ->get();

        $blocks = CabanaAvailability::where('cabana_id', $cabanaId)
            ->where('end_date', '>', $today)
            ->select('id', 'start_date', 'end_date', 'reason')
            ->get();

        return [
            'booked' => $this->formatDateRanges($bookings, 'check_in', 'check_out', 'booking_id'),
            'blocked' => $this->formatDateRanges($blocks, 'start_date', 'end_date', 'block_id'),
        ];
    }

    /**
     * Expand date ranges into individual date strings for easy frontend consumption.
     */
    private function formatDateRanges(Collection $records, string $startField, string $endField, string $idKey): array
    {
        $dates = [];

        foreach ($records as $record) {
            $start = Carbon::parse($record->$startField);
            $end = Carbon::parse($record->$endField);

            // Inclusive of start date, exclusive of checkout date (since they can check out while another checks in)
            while ($start->lt($end)) {
                $dates[] = [
                    'date' => $start->toDateString(),
                    $idKey => $record->id,
                ];
                $start->addDay();
            }
        }

        return $dates;
    }

    /**
     * Block dates for a cabana by an admin.
     * Prevents blocking if already booked overlapping.
     */
    public function blockDates(int $cabanaId, string $startDate, string $endDate, string $reason): CabanaAvailability
    {
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');

        // Validate it doesn't overlap with existing confirmed/pending bookings
        $hasOverlappingBookings = Booking::where('cabana_id', $cabanaId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($start, $end) {
            $query->where('check_in', '<', $end)
                ->where('check_out', '>', $start);
        })
            ->exists();

        if ($hasOverlappingBookings) {
            throw new \Exception('Cannot block dates that overlap with existing bookings.');
        }

        return CabanaAvailability::create([
            'cabana_id' => $cabanaId,
            'start_date' => $start,
            'end_date' => $end,
            'reason' => $reason
        ]);
    }

    /**
     * Remove an admin block.
     */
    public function removeBlock(int $blockId): bool
    {
        $block = CabanaAvailability::findOrFail($blockId);
        return $block->delete();
    }
}
