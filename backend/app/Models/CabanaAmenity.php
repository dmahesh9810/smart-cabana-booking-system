<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabanaAmenity extends Model
{
    protected $fillable = ['cabana_id', 'amenity_id'];
    public $timestamps = false;
}
