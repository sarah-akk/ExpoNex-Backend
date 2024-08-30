<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'username',
        'channel_id',
        'is_verified',
        'is_pending',
        'phone_number',
        'password',

        //TODO delete this
        'is_verified',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function scopeFilter($query)
    {
        $query->
            when(request()->role ?? null, function ($query, $role) {
                $query->where('role_id', $role);
            });
    }
    protected function permissions(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->role?->permissions->pluck('api_name')->toArray(),
        );
    }

    protected function profilePicture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->pictures()->where('type', 'profile')->latest()->first(),
        );
    }

    public function findForPassport(string $username): User
    {
        return $this->where('username', $username)->orWhere('email', $username)->first();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function company()
    {
        return $this->hasOne(Company::class);
    }
    public function pictures()
    {
        return $this->morphMany(Picture::class, 'commentable');
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function exhibitions()
    {
        return $this->hasMany(Exhibition::class, 'owner_id');
    }
}
