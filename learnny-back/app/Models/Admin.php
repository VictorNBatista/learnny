<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Admin Model
 * 
 * Represents an administrator user with system management capabilities. Admin accounts manage
 * subjects/courses, approve professor registrations, and monitor platform operations.
 * 
 * Admin authentication uses manual password verification (Hash::check) instead of Laravel's
 * standard Auth facade due to custom implementation requirements. Password is hashed at storage
 * to prevent plaintext exposure in database.
 * 
 * @property int $id
 * @property string $name Administrator's full name
 * @property string $email Admin account email address
 * @property string $password Hashed password
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
            'password' => 'hashed',
        ];
    }
}