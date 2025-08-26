<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professor extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo_url',
        'contact',
        'biography',
        'price',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'professor_subject');
    }
}
