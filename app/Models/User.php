<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // TEACHER, STUDENT, ADMIN
        'teacher_code',
        'teacher_id',
        'status', // ACTIVE, SUSPENDED
        'avatar',
        'locale', // ar, en
        'bio',
        'phone',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'guardian_relation',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isTeacher(): bool
    {
        return $this->role === 'TEACHER';
    }

    public function isStudent(): bool
    {
        return $this->role === 'STUDENT';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'teacher_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function generateTeacherCode(): string
    {
        if ($this->isTeacher() && empty($this->teacher_code)) {
            do {
                $code = 'TCH-' . strtoupper(Str::random(5));
            } while (self::where('teacher_code', $code)->exists());

            $this->update(['teacher_code' => $code]);
        }
        return $this->teacher_code ?? '';
    }

    public function gamification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GamificationProfile::class);
    }

    public function studyTasks(): HasMany
    {
        return $this->hasMany(StudyTask::class, 'student_id');
    }

    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    public function feedbackReports(): HasMany
    {
        return $this->hasMany(FeedbackReport::class, 'user_id');
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class, 'user_id');
    }

    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_completions', 'student_id', 'lesson_id')->withTimestamps();
    }

    public function getXpAttribute(): int
    {
        return $this->gamification?->xp ?? 0;
    }

    public function getLevelAttribute(): int
    {
        return $this->gamification?->level ?? max(1, floor($this->xp / 200) + 1);
    }

    public function getStreakAttribute(): int
    {
        return $this->gamification?->streak_count ?? 0;
    }

    public function addXp(int $amount): void
    {
        $profile = $this->gamification()->firstOrCreate([], [
            'xp' => 100,
            'level' => 1,
            'streak_count' => 1,
            'last_activity_date' => now()->toDateString(),
        ]);
        $profile->increment('xp', $amount);
        $newLevel = max(1, floor($profile->xp / 200) + 1);
        if ($newLevel > $profile->level) {
            $profile->update(['level' => $newLevel]);
        }
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        // Default initial avatar generator
        $bg = substr(md5($this->name), 0, 6);
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background={$bg}&color=fff&size=128";
    }

    // Teacher Relations
    public function createdClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function createdAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function createdMaterials(): HasMany
    {
        return $this->hasMany(Material::class, 'teacher_id');
    }

    public function createdQuizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // Student Relations
    public function enrolledClassrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_members', 'user_id', 'classroom_id')
                    ->withPivot('status', 'joined_at')
                    ->withTimestamps();
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    // Chat Relations
    public function sentPrivateMessages(): HasMany
    {
        return $this->hasMany(PrivateMessage::class, 'sender_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'student_id');
    }

    public function guardianNotifications(): HasMany
    {
        return $this->hasMany(GuardianNotification::class, 'student_id')->latest();
    }
}
