<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Professor Model
 * 
 * Represents an instructor/professor on the platform. Professors offer lesson appointments
 * in subjects they specialize in, with rate and availability management.
 * 
 * The model implements a status workflow: pending → approved → active teaching, or rejected.
 * Only approved professors can authenticate and book appointments. Integration with Moodle LMS
 * stores the moodle_id for course management and enrollment.
 * 
 * Soft deletes preserve historical appointment and availability data for record-keeping
 * when professor accounts are deleted.
 * 
 * @property int $id
 * @property string $name Professor's full name
 * @property string $password Hashed password
 * @property string $email Professional email address
 * @property string $username Unique username for login
 * @property int $moodle_id ID in Moodle LMS system (set after provisioning)
 * @property string $status Workflow status: 'pending', 'approved', 'rejected'
 * @property string $contact Phone or contact information
 * @property string $biography Professional biography/description
 * @property float $price Hourly rate for lessons
 * @property string $photo_url URL to professor's profile photo
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete timestamp
 * 
 * @relationship belongsToMany subjects Professor teaches many subjects (many-to-many via professor_subject table)
 * @relationship hasMany appointments Professor has many appointments scheduled with students
 * @relationship hasMany availabilities Professor has availability rules defining teaching hours
 */
class Professor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'password',
        'email',
        'username',  
        'moodle_id',
        'status',
        'contact',   
        'biography',
        'price',  
        'photo_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get all subjects this professor teaches
     * 
     * Many-to-many relationship defining the subjects/courses this professor specializes in
     * and is qualified to teach. Subjects are managed through the professor_subject pivot table.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'professor_subject');
    }

    /**
     * Get all appointments scheduled for this professor
     * 
     * Retrieves all lesson appointments where this professor is the instructor. Includes
     * appointments in various states (pending, confirmed, completed, cancelled).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all availability rules for this professor
     * 
     * Retrieves the recurrent availability/teaching hour rules (by day_of_week and time range)
     * that define when this professor is available to accept appointments.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
}
