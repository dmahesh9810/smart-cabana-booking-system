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
     * Accepts: start_date, end_date, status, cabana_id
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'cabana', 'payment']);

        // Date Range Filter — use filled() so empty strings are ignored
        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $start = Carbon::parse($request->start_date)->startOfDay()->toDateTimeString();
                $end   = Carbon::parse($request->end_date)->endOfDay()->toDateTimeString();
                $query->whereBetween('check_in', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Invalid date format.'], 422);
            }
        }

        // Cabana Filter
        if ($request->filled('cabana_id')) {
            $query->where('cabana_id', $request->cabana_id);
        }

        // Status Filter — filled() prevents empty string from being applied
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Use get() — reports need all matching records, not a paginated subset
        $bookings = $query->latest()->get();

        // Map to a consistent, structured response the frontend can rely on
        $data = $bookings->map(function ($booking) {
            return [
                'id'           => $booking->id,
                'booking_ref'  => $booking->booking_ref,
                'cabana'       => $booking->cabana ? ['name' => $booking->cabana->name] : null,
                'user'         => $booking->user  ? ['name' => $booking->user->name]   : null,
                'check_in'     => $booking->check_in,
                'check_out'    => $booking->check_out,
                'total_amount' => $booking->total_amount ?? 0,
                'status'       => $booking->status,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $data->count(),
            'data'    => $data,
        ]);
    }

    /**
     * Revenue Report API
     */
    public function revenue(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()->toDateTimeString()
            : Carbon::now()->startOfYear()->toDateTimeString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()->toDateTimeString()
            : Carbon::now()->endOfDay()->toDateTimeString();

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
            'data'    => $revenueData,
        ]);
    }

    /**
     * Cabana Occupancy Report API
     */
    public function occupancy(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->toDateString()
            : Carbon::now()->subDays(30)->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->toDateString()
            : Carbon::now()->toDateString();

        $start     = Carbon::parse($startDate);
        $end       = Carbon::parse($endDate);
        $totalDays = max(1, $start->diffInDays($end) + 1);

        $cabanas      = Cabana::where('is_active', true)->get();
        $occupancyData = [];

        foreach ($cabanas as $cabana) {
            $bookedDays = DB::table('bookings')
                ->where('cabana_id', $cabana->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereNull('deleted_at')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('check_in', [$startDate, $endDate])
                      ->orWhereBetween('check_out', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where('check_in', '<=', $startDate)
                              ->where('check_out', '>=', $endDate);
                      });
                })
                ->get()
                ->sum(function ($booking) use ($startDate, $endDate) {
                    $bStart = Carbon::parse($booking->check_in);
                    $bEnd   = Carbon::parse($booking->check_out);

                    $overlapStart = $bStart->max(Carbon::parse($startDate));
                    $overlapEnd   = $bEnd->min(Carbon::parse($endDate));

                    return max(0, $overlapStart->diffInDays($overlapEnd));
                });

            $occupancyData[] = [
                'cabana_name'          => $cabana->name,
                'total_days'           => $totalDays,
                'booked_days'          => $bookedDays,
                'occupancy_percentage' => min(100.00, round(($bookedDays / $totalDays) * 100, 2)),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $occupancyData,
        ]);
    }

    /**
     * Export Bookings to Excel
     * Uses filled() so empty filter params are ignored — mirrors the bookings() method
     */
    public function exportBookings(Request $request)
    {
        $query = Booking::with(['user', 'cabana']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $start = Carbon::parse($request->start_date)->startOfDay()->toDateTimeString();
                $end   = Carbon::parse($request->end_date)->endOfDay()->toDateTimeString();
                $query->whereBetween('check_in', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Invalid date format.'], 422);
            }
        }

        if ($request->filled('cabana_id')) {
            $query->where('cabana_id', $request->cabana_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'bookings_report_' . date('Y_m_d_His') . '.xlsx';

        return Excel::download(new BookingsExport($query), $filename);
    }

    /**
     * Export Revenue to Excel/PDF
     */
    public function exportRevenue(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()->toDateTimeString()
            : Carbon::now()->startOfYear()->toDateTimeString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()->toDateTimeString()
            : Carbon::now()->endOfDay()->toDateTimeString();

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

        if ($request->filled('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.revenue', [
                'data'      => $revenueData,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);
            return $pdf->download('revenue_report_' . date('Y_m_d') . '.pdf');
        }

        return Excel::download(new RevenueExport($revenueData), 'revenue_report_' . date('Y_m_d') . '.xlsx');
    }

    /**
     * Export Occupancy to Excel (placeholder)
     */
    public function exportOccupancy(Request $request)
    {
        return response()->json(['message' => 'Not implemented yet'], 501);
    }
}
