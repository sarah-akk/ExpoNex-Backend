<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'map',
        'block_size',
        'width',
        'height'
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
