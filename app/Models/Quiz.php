<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'classroom_id',
        'course_id',
        'teacher_id',
        'title',
        'description',
        'code',
        'duration_minutes',
        'passing_score',
        'difficulty',
        'randomize_questions',
        'randomize_answers',
        'allow_retry',
        'max_attempts',
        'status', // PUBLISHED, DRAFT
        'has_per_question_timer',
        'per_question_seconds',
        'is_ai_proctored',
        'pdf_exam_path',
        'pdf_exam_name',
    ];

    protected function casts(): array
    {
        return [
            'has_per_question_timer' => 'boolean',
            'is_ai_proctored' => 'boolean',
            'randomize_questions' => 'boolean',
            'randomize_answers' => 'boolean',
            'allow_retry' => 'boolean',
            'per_question_seconds' => 'integer',
            'duration_minutes' => 'integer',
            'passing_score' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class)->latest();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
