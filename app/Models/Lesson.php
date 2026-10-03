<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'video_url',
        'file_path',
        'file_name',
        'duration_minutes',
        'order',
        'is_free',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'duration_minutes' => 'integer',
        'order' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_completions', 'lesson_id', 'student_id')->withTimestamps();
    }

    public function isCompletedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->completedByUsers()->where('users.id', $user->id)->exists();
    }
}
