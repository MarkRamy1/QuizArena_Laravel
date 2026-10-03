<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackReport extends Model
{
    protected $fillable = [
        'user_id',
        'type', // QUESTION, QUIZ, LESSON, COURSE, GENERAL
        'item_id',
        'title',
        'description',
        'status', // PENDING, REVIEWED, RESOLVED
        'admin_notes',
        'resolved_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
