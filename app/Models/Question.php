<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'type', // CHOOSE, TRUE_FALSE, ESSAY, COMPLETE, MATCH
        'difficulty', // EASY, MEDIUM, HARD
        'question_text',
        'points',
        'time_limit_seconds',
        'order',
        'explanation',
        'model_answer',
        'match_pairs',
    ];

    protected function casts(): array
    {
        return [
            'match_pairs' => 'array',
            'points' => 'integer',
            'time_limit_seconds' => 'integer',
            'order' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
