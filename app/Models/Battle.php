<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Battle extends Model
{
    protected $fillable = [
        'code',
        'mode', // STUDENT_VS_AI, STUDENT_VS_STUDENT
        'status', // WAITING, IN_PROGRESS, COMPLETED, ABANDONED
        'started_at',
        'ended_at',
        'winner_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BattleParticipant::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(BattleRound::class);
    }
}
