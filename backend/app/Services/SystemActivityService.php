<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SystemActivityService
{
    /**
     * Full analytics stats for the admin dashboard.
     */
    public function getStats(): array
    {
        $now       = Carbon::now();
        $thisMonth = Carbon::now()->startOfMonth();
        $avgRating = DB::table('reviews')->avg('rating') ?? 0;

        // Bookings this month
        $bookingsThisMonth = Booking::where('created_at', '>=', $thisMonth)->count();

        // Revenue this month (from paid payments)
        $revenueThisMonth = Payment::where('payment_status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        // Most booked cabana (by confirmed booking count)
        $mostBooked = DB::table('bookings')
            ->select('cabana_id', DB::raw('COUNT(*) as booking_count'))
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNull('deleted_at')
            ->groupBy('cabana_id')
            ->orderByDesc('booking_count')
            ->first();

        $mostBookedCabana = null;
        if ($mostBooked) {
            $cab = Cabana::find($mostBooked->cabana_id);
            $mostBookedCabana = $cab ? [
                'id'            => $cab->id,
                'name'          => $cab->name,
                'booking_count' => $mostBooked->booking_count,
            ] : null;
        }

        // Top rated cabana (average review rating)
        $topRated = DB::table('reviews')
            ->select('cabana_id', DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(*) as review_count'))
            ->groupBy('cabana_id')
            ->orderByDesc('avg_rating')
            ->first();

        $topRatedCabana = null;
        if ($topRated) {
            $cab = Cabana::find($topRated->cabana_id);
            $topRatedCabana = $cab ? [
                'id'           => $cab->id,
                'name'         => $cab->name,
                'avg_rating'   => round((float) $topRated->avg_rating, 1),
                'review_count' => $topRated->review_count,
            ] : null;
        }

        // Bookings per month for the last 6 months (for charts)
        $bookingsPerMonth = [];
        $revenuePerMonth  = [];
        for ($i = 5; $i >= 0; $i--) {
            $month     = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd  = $month->copy()->endOfMonth();
            $label     = $month->format('M Y');

            $bookingsPerMonth[$label] = Booking::whereBetween('created_at', [$month, $monthEnd])->count();

            $revenuePerMonth[$label] = Payment::where('payment_status', 'paid')
                ->whereBetween('created_at', [$month, $monthEnd])
                ->sum('amount');
        }

        return [
            // Totals
            'total_users'           => User::count(),
            'total_cabanas'         => Cabana::count(),
            'active_cabanas'        => Cabana::where('is_active', true)->count(),
            'total_bookings'        => Booking::count(),
            'confirmed_bookings'    => Booking::where('status', 'confirmed')->count(),
            'total_revenue'         => Payment::where('payment_status', 'paid')->sum('amount'),
            'average_rating'        => round((float) $avgRating, 1),
            'upcoming_bookings'     => Booking::where('status', 'confirmed')
                ->where('check_in', '>=', Carbon::today()->toDateString())->count(),

            // This month
            'bookings_this_month'   => $bookingsThisMonth,
            'revenue_this_month'    => $revenueThisMonth,

            // Highlights
            'most_booked_cabana'    => $mostBookedCabana,
            'top_rated_cabana'      => $topRatedCabana,

            // Financial Split Statistics
            'commission_stats'      => (new \App\Services\CommissionService())->getAnalytics(),

            // Chart series (last 6 months)
            'bookings_per_month'    => $bookingsPerMonth,
            'revenue_per_month'     => $revenuePerMonth,
        ];
    }

    public function getBookings(int $perPage = 15): LengthAwarePaginator
    {
        return Booking::with(['user', 'cabana', 'payment'])->latest()->paginate($perPage);
    }

    public function getPayments(int $perPage = 15): LengthAwarePaginator
    {
        return Payment::with(['booking'])->latest()->paginate($perPage);
    }
}