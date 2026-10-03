<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattleParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'battle_id',
        'user_id',
        'is_ai',
        'score',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'is_ai' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
