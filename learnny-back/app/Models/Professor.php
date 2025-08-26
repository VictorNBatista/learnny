<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Professor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
