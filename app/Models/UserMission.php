<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMission extends Model
{
    protected $fillable = [
        'user_id',
        'mission_id',
        'progress',
        'is_claimed',
        'mission_date',
    ];

    protected $casts = [
        'progress' => 'integer',
        'is_claimed' => 'boolean',
        'mission_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(DailyMission::class, 'mission_id');
    }
}
