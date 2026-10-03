<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for clean purge
        Schema::disableForeignKeyConstraints();

        // Truncate tables with fake/pre-loaded data
        $tables = [
            'attempt_answers',
            'quiz_attempts',
            'certificates',
            'question_options',
            'question_choices',
            'questions',
            'live_quiz_sessions',
            'quizzes',
            'lesson_completions',
            'lessons',
            'courses',
            'assignment_submissions',
            'assignments',
            'materials',
            'classroom_messages',
            'classroom_members',
            'classrooms',
            'private_messages',
            'notifications',
            'guardian_notifications',
            'feedback_reports',
            'study_tasks',
            'user_missions',
            'daily_missions',
            'user_achievements',
            'achievements',
            'gamification_profiles',
            'ai_messages',
            'ai_conversations',
            'audit_logs',
            'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        // Create the ONLY single Master Admin account requested by the user
        $admin = User::create([
            'name' => 'المشرف العام (Admin)',
            'email' => 'admin@quizarena.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'locale' => 'ar',
            'bio' => 'المسؤول والمشرف العام على منصة QuizArena.',
        ]);

        $this->command->info("✓ Clean Database initialized successfully. Master Admin created: {$admin->email}");
    }
}
