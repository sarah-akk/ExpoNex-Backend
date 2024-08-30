<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'email',
        'pin_code',
        'type',
        'expire_at'
    ];
    protected function casts(): array
    {
        return [
            'pin_code' => 'hashed',
        ];
    }
}
