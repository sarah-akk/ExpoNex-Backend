<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'map_id',
        'type',
        'positions',
        'size',
        'price',
    ];

    protected $hidden = [
        'company_id',
        'map_id',
        'created_at',
        'updated_at',
    ];

    public function positions(): Attribute
    {
        return Attribute::make(
            get: fn($data) => unserialize($data)
        );
    }

    public function map()
    {
        return $this->belongsTo(Map::class);
    }
    public function auctions()
    {
        return $this->belongsToMany(Company::class)->withPivot('price');
    }
}
