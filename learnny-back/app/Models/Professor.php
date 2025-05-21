<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'name',
        'photo_url',
        'contact',
        'biography',
        'subject',
        'price',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'professor_subject');
    }
}