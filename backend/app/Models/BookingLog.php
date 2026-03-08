<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingLog extends Model
{
    protected $fillable = ['booking_id', 'action', 'notes'];
    public $timestamps = false;

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
