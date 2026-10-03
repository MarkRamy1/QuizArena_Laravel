<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMission extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type', // QUIZ, LESSON, SCORE, STREAK
        'target_count',
        'xp_reward',
    ];

    protected $casts = [
        'target_count' => 'integer',
        'xp_reward' => 'integer',
    ];

    public function userMissions(): HasMany
    {
        return $this->hasMany(UserMission::class, 'mission_id');
    }
}
