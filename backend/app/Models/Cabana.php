<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cabana extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'price_per_night',
        'max_guests', 'location', 'is_active'
    ];

    public function images()
    {
        return $this->hasMany(CabanaImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(CabanaImage::class)->where('is_primary', true);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class , 'cabana_amenities');
    }

    public function availabilities()
    {
        return $this->hasMany(CabanaAvailability::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
