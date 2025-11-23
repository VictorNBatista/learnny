<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Availability Model
 * 
 * Represents a recurring availability rule defining when a professor is available to teach.
 * Rules are defined by day of week (0-6, where 0=Sunday) and time range (start/end times).
 * 
 * These rules are used by AvailabilityService to generate available appointment slots,
 * which are further filtered to exclude already-confirmed appointments. This enables
 * professors to set their general teaching schedule once, avoiding manual daily slot entry.
 * 
 * Example: A professor available Mon-Fri 14:00-18:00 creates 5 availability records:
 * - (day_of_week=1, start_time='14:00', end_time='18:00') for Monday
 * - (day_of_week=2, start_time='14:00', end_time='18:00') for Tuesday
 * - etc.
 * 
 * @property int $id
 * @property int $professor_id Foreign key to professors table
 * @property int $day_of_week Day of week (0=Sunday, 1=Monday, ... 6=Saturday)
 * @property string $start_time HH:MM time format when professor is available (e.g., '14:00')
 * @property string $end_time HH:MM time format when professor's availability ends
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @relationship belongsTo professor The professor who has this availability rule
 */
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
     * Get the professor this availability rule belongs to
     * 
     * Inverse of Professor::availabilities(). Returns the Professor who owns this
     * teaching availability schedule rule.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }
}