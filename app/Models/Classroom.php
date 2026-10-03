<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    protected $fillable = [
        'teacher_id',
        'name',
        'subject',
        'grade',
        'description',
        'code',
        'theme_color',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ClassroomMember::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_members', 'classroom_id', 'user_id')
                    ->wherePivot('status', 'ACCEPTED')
                    ->withPivot('status', 'joined_at')
                    ->withTimestamps();
    }

    public function pendingRequests(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_members', 'classroom_id', 'user_id')
                    ->wherePivot('status', 'PENDING')
                    ->withPivot('status', 'joined_at')
                    ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClassroomMessage::class)->oldest();
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class)->latest();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->latest();
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class)->latest();
    }
}
