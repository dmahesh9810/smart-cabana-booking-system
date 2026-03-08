<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_ref', 'user_id', 'cabana_id',
        'check_in', 'check_out', 'guests_count',
        'total_amount', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cabana()
    {
        return $this->belongsTo(Cabana::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function logs()
    {
        return $this->hasMany(BookingLog::class);
    }
}
