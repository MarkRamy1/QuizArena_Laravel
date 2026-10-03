<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuizSession extends Model
{
    protected $fillable = [
        'quiz_id',
        'teacher_id',
        'pin_code',
        'status', // WAITING, ACTIVE, FINISHED
        'current_question_index',
        'participants',
    ];

    protected $casts = [
        'participants' => 'array',
        'current_question_index' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
