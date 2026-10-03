<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationProfile extends Model
{
    protected $fillable = [
        'user_id',
        'xp',
        'level',
        'streak_count',
        'last_activity_date',
        'badges',
    ];

    protected $casts = [
        'xp' => 'integer',
        'level' => 'integer',
        'streak_count' => 'integer',
        'badges' => 'array',
        'last_activity_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
