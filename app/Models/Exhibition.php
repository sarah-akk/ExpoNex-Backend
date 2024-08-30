<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhibition extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'start_at',
        'end_at',
        'status',
        'location',
        'size',
        'description',
        'coordinates',
    ];

    protected function profilePicture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->pictures()->where('type', 'exhibition-profile')->latest()->first(),
        );
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function docs()
    {
        return $this->morphMany(Document::class, 'commentable');
    }
    public function pictures()
    {
        return $this->morphMany(Picture::class, 'commentable');
    }
    public function ticketManager()
    {
        return $this->hasOne(Ticket::class);
    }
    public function mapManager()
    {
        return $this->hasOne(Map::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
