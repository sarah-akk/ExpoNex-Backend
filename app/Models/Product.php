<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'exhibition_id',
        'company_id',
        'name',
        'description',
        'price',
        'quantity'
    ];

    public function scopeFilter($query)
    {
        $query->
            when(request()->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            });

        $query->
            when(request()->user()->role_id === 2 && request()->expo_id, function ($query, $expo_id) {
                $query->where('exhibition_id', request()->expo_id);
            });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function exhibition()
    {
        return $this->belongsT(Exhibition::class);
    }
    public function pictures()
    {
        return $this->morphMany(Picture::class, 'commentable');
    }
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }
}
