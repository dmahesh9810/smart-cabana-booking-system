<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelUnpaidBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-unpaid-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel bookings pending payment for more than 15 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->whereHas('payment', function ($query) {
                $query->where('payment_status', 'pending');
            })
            ->get();

        $count = 0;

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking, &$count) {
                $booking->update(['status' => 'cancelled']);
                
                if ($booking->payment) {
                    $booking->payment->update(['payment_status' => 'failed']);
                }

                Log::info("Booking {$booking->booking_ref} cancelled due to payment timeout.");
                $count++;
            });
        }

        $this->info("Cancelled {$count} unpaid bookings.");
    }
}
