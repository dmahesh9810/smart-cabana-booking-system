<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Commission;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BookingsExport;
use App\Exports\RevenueExport;

class AdminReportController extends Controller
{
    /**
     * Booking Report API
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'cabana', 'payment']);

        // Date Range Filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('check_in', [$request->start_date, $request->end_date]);
        }

        // Cabana Filter
        if ($request->has('cabana_id')) {
            $query->where('cabana_id', $request->cabana_id);
        }

        // Status Filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Revenue Report API
     */
    public function revenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->toDateTimeString());
        $endDate = $request->input('end_date', Carbon::now()->endOfDay()->toDateTimeString());

        $monthFormat = DB::getDriverName() === 'sqlite' 
            ? "strftime('%m %Y', created_at)" 
            : "DATE_FORMAT(created_at, '%M %Y')";

        $revenueData = DB::table('commissions')
            ->select(
                DB::raw("$monthFormat as month"),
                DB::raw('SUM(gross_amount) as gross_revenue'),
                DB::raw('SUM(commission_amount) as platform_commission'),
                DB::raw('SUM(owner_earnings) as owner_earnings')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $revenueData
        ]);
    }

    /**
     * Cabana Occupancy Report API
     */
    public function occupancy(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalDays = $start->diffInDays($end) + 1;

        $cabanas = Cabana::where('is_active', true)->get();
        $occupancyData = [];

        foreach ($cabanas as $cabana) {
            // Calculate booked days within the range
            $bookedDays = DB::table('bookings')
                ->where('cabana_id', $cabana->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('check_in', [$startDate, $endDate])
                      ->orWhereBetween('check_out', [$startDate, $endDate])
                      ->orWhere(function($sub) use ($startDate, $endDate) {
                          $sub->where('check_in', '<=', $startDate)
                              ->where('check_out', '>=', $endDate);
                      });
                })
                ->get()
                ->sum(function($booking) use ($startDate, $endDate) {
                    $bStart = Carbon::parse($booking->check_in);
                    $bEnd = Carbon::parse($booking->check_out);
                    
                    $overlapStart = $bStart->max(Carbon::parse($startDate));
                    $overlapEnd = $bEnd->min(Carbon::parse($endDate));
                    
                    return $overlapStart->diffInDays($overlapEnd);
                });

            $occupancyPercentage = $totalDays > 0 ? round(($bookedDays / $totalDays) * 100, 2) : 0;

            $occupancyData[] = [
                'cabana_name' => $cabana->name,
                'total_days' => $totalDays,
                'booked_days' => $bookedDays,
                'occupancy_percentage' => min(100.00, $occupancyPercentage)
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $occupancyData
        ]);
    }

    /**
     * Export Bookings to Excel
     */
    public function exportBookings(Request $request)
    {
        $query = Booking::with(['user', 'cabana']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('check_in', [$request->start_date, $request->end_date]);
        }

        if ($request->has('cabana_id')) {
            $query->where('cabana_id', $request->cabana_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return Excel::download(new BookingsExport($query), 'bookings_report_' . date('Y_m_d') . '.xlsx');
    }

    /**
     * Export Revenue to Excel/PDF
     */
    public function exportRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->toDateTimeString());
        $endDate = $request->input('end_date', Carbon::now()->endOfDay()->toDateTimeString());

        $monthFormat = DB::getDriverName() === 'sqlite' 
            ? "strftime('%m %Y', created_at)" 
            : "DATE_FORMAT(created_at, '%M %Y')";

        $revenueData = DB::table('commissions')
            ->select(
                DB::raw("$monthFormat as month"),
                DB::raw('SUM(gross_amount) as gross_revenue'),
                DB::raw('SUM(commission_amount) as platform_commission'),
                DB::raw('SUM(owner_earnings) as owner_earnings')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('created_at')
            ->get();

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.revenue', [
                'data' => $revenueData,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            return $pdf->download('revenue_report_' . date('Y_m_d') . '.pdf');
        }

        return Excel::download(new RevenueExport($revenueData), 'revenue_report_' . date('Y_m_d') . '.xlsx');
    }

    /**
     * Export Occupancy to Excel
     */
    public function exportOccupancy(Request $request)
    {
        return response()->json(['message' => 'Not implemented yet'], 501);
    }
}
