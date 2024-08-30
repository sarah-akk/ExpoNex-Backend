<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'in_place',
        'available_in_place',
        'in_place_price',
        'in_virtual_price',
        'prime',
        'available_prime',
        'prime_price',
        'barcode',
        'side_style',
        'main_style',
        'title',
        'description',

    ];

    protected function mainPicture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->pictures()->where('type', 'ticket-main')->latest()->first(),
        );
    }
    protected function sidePicture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->pictures()->where('type', 'ticket-side')->latest()->first(),
        );
    }
    public function expo()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function pictures()
    {
        return $this->morphMany(Picture::class, 'commentable');
    }
    public function ticketItems()
    {
        return $this->hasMany(TicketItems::class);
    }

}
