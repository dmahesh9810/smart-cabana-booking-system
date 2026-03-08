<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabanaAvailability extends Model
{
    protected $table = 'cabana_availability';
    protected $fillable = ['cabana_id', 'start_date', 'end_date', 'reason'];

    public function cabana()
    {
        return $this->belongsTo(Cabana::class);
    }
}
