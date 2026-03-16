<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Status',
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->booking_ref                                       ?? 'N/A',
            optional($booking->cabana)->name                            ?? 'N/A',
            optional($booking->user)->name                              ?? 'N/A',
            $booking->check_in                                          ?? '',
            $booking->check_out                                         ?? '',
            number_format((float) ($booking->total_amount ?? 0), 2),
            ucfirst($booking->status                                    ?? ''),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Bold the header row
            1 => ['font' => ['bold' => true]],
        ];
    }
}
