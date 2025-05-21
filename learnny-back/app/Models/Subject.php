<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Futuro relacionamento com Professor
    public function professors()
    {
        return $this->hasMany(Professor::class);
    }
}
