<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'professor_id',
        'subject_id',
        'start_time',
        'end_time',
        'price_paid',
        'location_details',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Obtém o aluno (User) que agendou a aula.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtém o professor da aula.
     */
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    /**
     * Obtém a matéria da aula.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}