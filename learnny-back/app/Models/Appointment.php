<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Appointment Model
 * 
 * Represents a scheduled lesson session between a student (User) and professor. This is the
 * central domain model tracking all lesson bookings, their participants, subject matter, timing,
 * and workflow state.
 * 
 * Appointments follow a workflow: pending (awaiting professor confirmation) → confirmed (locked in)
 * → completed (lesson delivered), or can be cancelled by either participant. Price is captured
 * at booking time to handle rate changes without affecting historical records.
 * 
 * Start/end times are cast to Carbon datetime objects for easy manipulation and timezone handling.
 * Location details can contain address, room number, or virtual meeting link information.
 * 
 * @property int $id
 * @property int $user_id ID of the student booking the appointment (foreign key to users table)
 * @property int $professor_id ID of the instructor (foreign key to professors table)
 * @property int $subject_id Subject/course being taught (foreign key to subjects table)
 * @property \Illuminate\Support\Carbon $start_time Lesson start time (UTC datetime)
 * @property \Illuminate\Support\Carbon $end_time Lesson end time (UTC datetime, typically 1 hour after start_time)
 * @property float $price_paid Amount paid at time of booking (locked historical value)
 * @property string $location_details Meeting location: physical address or virtual meeting URL
 * @property string $status Workflow state: 'pending', 'confirmed', 'completed', 'cancelled_by_user', 'cancelled_by_professor'
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @relationship belongsTo user The student who booked this appointment
 * @relationship belongsTo professor The instructor teaching this appointment
 * @relationship belongsTo subject The subject matter of the lesson
 */
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
     * Get the student who booked this appointment
     * 
     * Inverse of User::appointments(). Returns the User model representing the student
     * who requested the lesson.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the professor/instructor for this appointment
     * 
     * Inverse of Professor::appointments(). Returns the Professor model representing the
     * instructor who will teach or has taught the lesson.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    /**
     * Get the subject matter for this appointment
     * 
     * Returns the Subject model representing what topic/course this appointment covers.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}