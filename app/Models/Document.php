<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'path',
        'title',
        'description',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }
}
