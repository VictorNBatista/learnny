<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Subject Model
 * 
 * Represents a subject/course offered on the platform (e.g., Mathematics, English, Physics).
 * Subjects are managed by administrators and serve as the lesson topic when students book
 * appointments with professors.
 * 
 * Many-to-many relationship with Professor allows multiple professors to teach the same subject,
 * and professors to specialize in multiple subjects. This facilitates subject discovery and
 * matching of appropriate instructors for student appointments.
 * 
 * @property int $id
 * @property string $name Subject name (e.g., 'Mathematics', 'English Literature')
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @relationship belongsToMany professors Professors who teach this subject (many-to-many via professor_subject table)
 */
class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get all professors who teach this subject
     * 
     * Many-to-many relationship returning all professors qualified to teach this subject.
     * Professors are linked through the professor_subject pivot table enabling flexible
     * subject-to-professor mapping (many professors per subject, many subjects per professor).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function professors()
    {
        return $this->belongsToMany(Professor::class, 'professor_subject');
    }
}
