<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyTask extends Model
{
    protected $fillable = [
        'student_id',
        'day_of_week',
        'subject',
        'topic',
        'duration_minutes',
        'is_completed',
        'notes',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
