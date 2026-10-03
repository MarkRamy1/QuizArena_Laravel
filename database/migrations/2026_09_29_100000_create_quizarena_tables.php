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
        // 1. Classrooms
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->string('grade')->nullable();
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->string('theme_color')->default('#4f46e5');
            $table->timestamps();
        });

        // 2. Classroom Members (Join via code or request to teacher)
        Schema::create('classroom_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('ACCEPTED'); // ACCEPTED, PENDING, REJECTED
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['classroom_id', 'user_id']);
        });

        // 3. Global Classroom Chat (Group discussion wall)
        Schema::create('classroom_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        // 4. Private 1-on-1 Chat (Student <-> Teacher)
        Schema::create('private_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->text('message');
            $table->string('attachment')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 5. Materials (Uploaded by teacher for students)
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // pdf, doc, video, link, etc.
            $table->integer('file_size')->nullable();
            $table->string('external_url')->nullable();
            $table->timestamps();
        });

        // 6. Assignments & Deadlines
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('file_path')->nullable(); // reference file from teacher
            $table->string('file_name')->nullable();
            $table->integer('max_points')->default(100);
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });

        // 7. Assignment Submissions (Student uploads work)
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable(); // uploaded work file
            $table->string('file_name')->nullable();
            $table->text('notes')->nullable();
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->string('status')->default('SUBMITTED'); // SUBMITTED, GRADED, LATE
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });

        // 8. Quizzes (belong to classroom or general)
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->integer('duration_minutes')->default(20);
            $table->integer('passing_score')->default(60);
            $table->string('status')->default('PUBLISHED'); // PUBLISHED, DRAFT
            $table->timestamps();
        });

        // 9. Questions (CHOOSE, TRUE_FALSE, ESSAY, COMPLETE, MATCH)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->string('type'); // CHOOSE, TRUE_FALSE, ESSAY, COMPLETE, MATCH
            $table->text('question_text');
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->text('explanation')->nullable();
            $table->text('model_answer')->nullable(); // For ESSAY, COMPLETE, or TRUE_FALSE
            $table->json('match_pairs')->nullable(); // For MATCH: [{"left": "...", "right": "..."}]
            $table->timestamps();
        });

        // 10. Question Options (for CHOOSE and TRUE_FALSE)
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 11. Quiz Attempts
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('IN_PROGRESS'); // IN_PROGRESS, SUBMITTED, GRADED
            $table->integer('score')->default(0);
            $table->integer('max_score')->default(0);
            $table->integer('percentage')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 12. Attempt Answers
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->text('student_answer')->nullable(); // text or json string for match
            $table->boolean('is_correct')->nullable();
            $table->integer('points_awarded')->default(0);
            $table->text('teacher_feedback')->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('private_messages');
        Schema::dropIfExists('classroom_messages');
        Schema::dropIfExists('classroom_members');
        Schema::dropIfExists('classrooms');
    }
};
