<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name', 'icon'];

    public function cabanas()
    {
        return $this->belongsToMany(Cabana::class , 'cabana_amenities');
    }
}
