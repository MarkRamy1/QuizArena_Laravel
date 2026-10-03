<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'teacher_id',
        'classroom_id',
        'title',
        'slug',
        'subject',
        'grade',
        'description',
        'thumbnail',
        'difficulty',
        'status',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function getProgressForUser(?User $user): int
    {
        if (!$user) return 0;
        $totalLessons = $this->lessons()->count();
        if ($totalLessons === 0) return 0;
        
        $completedCount = $user->completedLessons()
            ->whereIn('lessons.id', $this->lessons()->pluck('id'))
            ->count();
            
        return (int) round(($completedCount / $totalLessons) * 100);
    }
}
