<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'Booking ID',
            'Cabana Name',
            'Guest Name',
            'Check In',
            'Check Out',
            'Total Price (LKR)',
            'Status'
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->booking_ref,
            $booking->cabana->name,
            $booking->user->name,
            $booking->check_in,
            $booking->check_out,
            number_format($booking->total_amount, 2),
            ucfirst($booking->status)
        ];
    }
}
