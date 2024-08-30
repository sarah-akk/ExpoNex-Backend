<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'companyname',
        'description',
    ];
    protected function profilePicture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->pictures()->where('type', 'company-profile')->latest()->first(),
        );
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function docs()
    {
        return $this->morphMany(Document::class, 'commentable');
    }
    public function pictures()
    {
        return $this->morphMany(Picture::class, 'commentable');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function auctions()
    {
        return $this->belongsToMany(Section::class)->withPivot('price');
    }
}
