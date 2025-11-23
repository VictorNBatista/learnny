<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * User Model
 * 
 * Represents a student/user in the learning platform. Users can book appointments with professors
 * for lesson sessions on various subjects. The model extends Authenticatable to support OAuth2
 * token-based authentication via Laravel Passport.
 * 
 * Soft deletes are enabled to maintain referential integrity of historical appointment records
 * when user accounts are deleted.
 * 
 * @property int $id
 * @property string $name Full name of the user/student
 * @property string $username Unique username for login
 * @property string $email User's email address
 * @property string $contact User's phone or contact information
 * @property string $password Hashed password
 * @property string $photo_url URL to user's profile photo
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete timestamp
 * 
 * @relationship hasMany appointments User has many appointments scheduled with professors
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'contact',
        'password',
        'photo_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all appointments booked by this user
     * 
     * Retrieves all appointments the student has scheduled with professors. May include
     * appointments in various states (pending, confirmed, completed, cancelled).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
