<?php
namespace App\Services;
use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Payment;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SystemActivityService
{
    public function getStats(): array
    {
        $avgRating = DB::table('reviews')->avg('rating') ?? 0;
        return [
            'total_cabanas' => Cabana::count(),
            'active_cabanas' => Cabana::where('is_active', true)->count(),
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'total_revenue' => Payment::where('status', 'successful')->sum('amount'),
            'average_rating' => round((float) $avgRating, 1),
            'upcoming_bookings' => Booking::where('status', 'confirmed')->where('check_in', '>=', Carbon::today()->toDateString())->count(),
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