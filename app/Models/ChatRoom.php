<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatRoom extends Model
{
    protected $fillable = [
        'name',
        'type', // GLOBAL, COURSE, DIRECT
        'course_id',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'room_id')->oldest();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_members', 'room_id', 'user_id')
                    ->withPivot('joined_at');
    }
}
