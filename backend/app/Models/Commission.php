<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    /** @use HasFactory<\Database\Factories\CommissionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id', 'gross_amount', 'commission_rate',
        'commission_amount', 'owner_earnings', 'status'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
