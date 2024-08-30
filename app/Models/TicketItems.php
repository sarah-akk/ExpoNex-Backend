<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'ticket_id',
        'type',
        'quantity',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
