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
        // 1. Add Guardian details to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('guardian_name')->nullable()->after('phone');
            $table->string('guardian_email')->nullable()->after('guardian_name');
            $table->string('guardian_phone')->nullable()->after('guardian_email');
            $table->string('guardian_relation')->nullable()->after('guardian_phone');
        });

        // 2. Add question timer, AI proctoring, and PDF exam to quizzes table
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('has_per_question_timer')->default(false)->after('duration_minutes');
            $table->integer('per_question_seconds')->default(0)->after('has_per_question_timer');
            $table->boolean('is_ai_proctored')->default(true)->after('per_question_seconds');
            $table->string('pdf_exam_path')->nullable()->after('is_ai_proctored');
            $table->string('pdf_exam_name')->nullable()->after('pdf_exam_path');
        });

        // 3. Add time limit per question to questions table
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('time_limit_seconds')->default(0)->after('points');
        });

        // 4. Add proctoring fields to quiz_attempts table
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('proctor_violations_count')->default(0)->after('percentage');
            $table->string('proctor_status')->default('PASSED_CLEAN')->after('proctor_violations_count'); // PASSED_CLEAN, FLAGGED_SUSPICIOUS, DISQUALIFIED
            $table->text('proctor_log')->nullable()->after('proctor_status');
        });

        // 5. Create certificates table
        if (!Schema::hasTable('certificates')) {
            Schema::create('certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->string('certificate_number');
                $table->string('verification_code')->unique();
                $table->integer('percentage');
                $table->timestamp('issued_at');
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Create guardian_notifications table
        if (!Schema::hasTable('guardian_notifications')) {
            Schema::create('guardian_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type'); // EXAM_SCORE, EXAM_MISSED, SESSION_MISSED, CUSTOM
                $table->string('guardian_name')->nullable();
                $table->string('guardian_email')->nullable();
                $table->string('guardian_phone')->nullable();
                $table->string('title');
                $table->text('message');
                $table->string('status')->default('SENT'); // SENT, DELIVERED
                $table->timestamp('sent_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_notifications');
        Schema::dropIfExists('certificates');

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['proctor_violations_count', 'proctor_status', 'proctor_log']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('time_limit_seconds');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['has_per_question_timer', 'per_question_seconds', 'is_ai_proctored', 'pdf_exam_path', 'pdf_exam_name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['guardian_name', 'guardian_email', 'guardian_phone', 'guardian_relation']);
        });
    }
};
