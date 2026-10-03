<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    protected $fillable = [
        'type', // COMPLETE_QUIZ, STUDY_RESOURCE, MAINTAIN_STREAK, etc.
        'title',
        'description',
        'goal',
        'xp_reward',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function userMissions(): HasMany
    {
        return $this->hasMany(UserMission::class);
    }
}
