<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;
use Exception;

class CommissionService
{
    protected float $defaultRate = 0.25;

    /**
     * Calculate and record commission for a completed booking.
     */
    public function recordCommission(Booking $booking): Commission
    {
        return DB::transaction(function () use ($booking) {
            // Ensure we don't duplicate commission for the same booking
            $existing = Commission::where('booking_id', $booking->id)->first();
            if ($existing) {
                return $existing;
            }

            $grossAmount = $booking->total_amount;
            $commissionAmount = $grossAmount * $this->defaultRate;
            $ownerEarnings = $grossAmount - $commissionAmount;

            return Commission::create([
                'booking_id' => $booking->id,
                'gross_amount' => $grossAmount,
                'commission_rate' => $this->defaultRate,
                'commission_amount' => $commissionAmount,
                'owner_earnings' => $ownerEarnings,
                'status' => 'pending'
            ]);
        });
    }

    /**
     * Get platform financial metrics.
     */
    public function getAnalytics(): array
    {
        return [
            'gross_revenue' => Commission::sum('gross_amount'),
            'platform_commission' => Commission::sum('commission_amount'),
            'pending_owner_payouts' => Commission::where('status', 'pending')->sum('owner_earnings'),
        ];
    }
}
