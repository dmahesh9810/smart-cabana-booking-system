<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannexMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabana_id',
        'property_id',
        'room_type_id',
        'rate_plan_id',
    ];

    public function cabana()
    {
        return $this->belongsTo(Cabana::class);
    }
}
