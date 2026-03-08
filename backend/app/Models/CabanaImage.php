<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabanaImage extends Model
{
    protected $fillable = ['cabana_id', 'image_path', 'is_primary'];

    public function cabana()
    {
        return $this->belongsTo(Cabana::class);
    }
}
