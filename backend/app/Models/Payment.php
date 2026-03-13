<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'order_id', 'amount', 'currency',
        'payment_method', 'payment_status', 'payhere_payment_id'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
