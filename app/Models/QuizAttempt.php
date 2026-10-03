<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'student_id',
        'status', // IN_PROGRESS, SUBMITTED, GRADED
        'score',
        'max_score',
        'percentage',
        'proctor_violations_count',
        'proctor_status', // PASSED_CLEAN, FLAGGED_SUSPICIOUS, DISQUALIFIED
        'proctor_log',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'max_score' => 'integer',
            'percentage' => 'integer',
            'proctor_violations_count' => 'integer',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class, 'attempt_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'attempt_id');
    }
}
