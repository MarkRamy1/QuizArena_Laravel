<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Courses
        if (!Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('subject');
                $table->string('grade')->nullable();
                $table->text('description')->nullable();
                $table->string('thumbnail')->nullable();
                $table->string('difficulty')->default('MEDIUM'); // EASY, MEDIUM, HARD
                $table->string('status')->default('PUBLISHED'); // PUBLISHED, DRAFT
                $table->timestamps();
            });
        }

        // 2. Lessons
        if (!Schema::hasTable('lessons')) {
            Schema::create('lessons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('video_url')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->integer('duration_minutes')->default(15);
                $table->integer('order')->default(1);
                $table->boolean('is_free')->default(true);
                $table->timestamps();
            });
        }

        // 3. Lesson Completions
        if (!Schema::hasTable('lesson_completions')) {
            Schema::create('lesson_completions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
                $table->timestamp('completed_at')->useCurrent();
                $table->timestamps();
                $table->unique(['student_id', 'lesson_id']);
            });
        }

        // 4. Study Tasks (Weekly Study Planner)
        if (!Schema::hasTable('study_tasks')) {
            Schema::create('study_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('day_of_week'); // Monday, Tuesday, etc.
                $table->string('subject');
                $table->string('topic');
                $table->integer('duration_minutes')->default(30);
                $table->boolean('is_completed')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Gamification Profiles
        if (!Schema::hasTable('gamification_profiles')) {
            Schema::create('gamification_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->integer('xp')->default(0);
                $table->integer('level')->default(1);
                $table->integer('streak_count')->default(1);
                $table->date('last_activity_date')->nullable();
                $table->json('badges')->nullable();
                $table->timestamps();
            });
        }

        // 6. Achievements
        if (!Schema::hasTable('achievements')) {
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('title');
                $table->text('description');
                $table->string('icon')->default('🏆');
                $table->integer('xp_reward')->default(50);
                $table->string('category')->default('GENERAL');
                $table->timestamps();
            });
        }

        // 7. User Achievements
        if (!Schema::hasTable('user_achievements')) {
            Schema::create('user_achievements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('achievement_id')->constrained('achievements')->cascadeOnDelete();
                $table->timestamp('unlocked_at')->useCurrent();
                $table->timestamps();
                $table->unique(['user_id', 'achievement_id']);
            });
        }

        // 8. Daily Missions
        if (!Schema::hasTable('daily_missions')) {
            Schema::create('daily_missions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description');
                $table->string('type')->default('QUIZ'); // QUIZ, LESSON, SCORE, STREAK
                $table->integer('target_count')->default(1);
                $table->integer('xp_reward')->default(50);
                $table->timestamps();
            });
        }

        // 9. User Missions
        if (!Schema::hasTable('user_missions')) {
            Schema::create('user_missions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('mission_id')->constrained('daily_missions')->cascadeOnDelete();
                $table->integer('progress')->default(0);
                $table->boolean('is_claimed')->default(false);
                $table->date('mission_date');
                $table->timestamps();
                $table->unique(['user_id', 'mission_id', 'mission_date']);
            });
        }

        // 10. Feedback Reports
        if (!Schema::hasTable('feedback_reports')) {
            Schema::create('feedback_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type')->default('GENERAL'); // QUESTION, QUIZ, LESSON, COURSE, GENERAL
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('title');
                $table->text('description');
                $table->string('status')->default('PENDING'); // PENDING, REVIEWED, RESOLVED
                $table->text('admin_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 11. Audit Logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action');
                $table->text('description');
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // 12. In-App Notifications
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('type')->default('INFO'); // INFO, SUCCESS, WARNING, QUIZ, COURSE, ACHIEVEMENT
                $table->string('url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        // 13. Live Quiz Sessions
        if (!Schema::hasTable('live_quiz_sessions')) {
            Schema::create('live_quiz_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->string('pin_code')->unique();
                $table->string('status')->default('WAITING'); // WAITING, ACTIVE, FINISHED
                $table->integer('current_question_index')->default(0);
                $table->json('participants')->nullable();
                $table->timestamps();
            });
        }

        // 14. Add extra columns to quizzes table if missing
        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('classroom_id')->constrained('courses')->nullOnDelete();
            }
            if (!Schema::hasColumn('quizzes', 'difficulty')) {
                $table->string('difficulty')->default('MEDIUM')->after('passing_score');
            }
            if (!Schema::hasColumn('quizzes', 'randomize_questions')) {
                $table->boolean('randomize_questions')->default(true)->after('difficulty');
            }
            if (!Schema::hasColumn('quizzes', 'randomize_answers')) {
                $table->boolean('randomize_answers')->default(true)->after('randomize_questions');
            }
            if (!Schema::hasColumn('quizzes', 'allow_retry')) {
                $table->boolean('allow_retry')->default(true)->after('randomize_answers');
            }
            if (!Schema::hasColumn('quizzes', 'max_attempts')) {
                $table->integer('max_attempts')->default(3)->after('allow_retry');
            }
        });

        // 15. Add difficulty column to questions table if missing
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'difficulty')) {
                $table->string('difficulty')->default('MEDIUM')->after('type'); // EASY, MEDIUM, HARD
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'difficulty')) {
                $table->dropColumn('difficulty');
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropColumn(['difficulty', 'randomize_questions', 'randomize_answers', 'allow_retry', 'max_attempts']);
        });

        Schema::dropIfExists('live_quiz_sessions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('feedback_reports');
        Schema::dropIfExists('user_missions');
        Schema::dropIfExists('daily_missions');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('gamification_profiles');
        Schema::dropIfExists('study_tasks');
        Schema::dropIfExists('lesson_completions');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('courses');
    }
};
