<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'serial_number' => 'hashed',
        ];
    }
}
