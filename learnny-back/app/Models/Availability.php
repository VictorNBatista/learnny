<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'professor_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    /**
     * Obtém o professor a quem esta disponibilidade pertence.
     */
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }
}