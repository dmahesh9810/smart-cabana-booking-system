<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CabanaImage extends Model
{
    use HasFactory;

    protected $fillable = ['cabana_id', 'image_path', 'is_primary'];

    public function cabana()
    {
        return $this->belongsTo(Cabana::class);
    }
}
