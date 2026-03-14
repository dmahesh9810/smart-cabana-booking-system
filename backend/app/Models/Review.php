<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'cabana_id', 'booking_id', 'rating', 'comment', 'is_approved'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cabana()
    {
        return $this->belongsTo(Cabana::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
