<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardianNotification extends Model
{
    protected $fillable = [
        'student_id',
        'classroom_id',
        'quiz_id',
        'teacher_id',
        'type', // EXAM_SCORE, EXAM_MISSED, SESSION_MISSED, CUSTOM
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'title',
        'message',
        'status', // SENT, DELIVERED
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
