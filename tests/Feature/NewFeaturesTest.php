<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use App\Models\GuardianNotification;

class NewFeaturesTest extends TestCase
{
    public function test_user_roles_system_has_student_teacher_admin(): void
    {
        // 1. Verify ADMIN user exists and has admin privileges
        $admin = User::where('role', 'ADMIN')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isStudent());
        $this->assertFalse($admin->isTeacher());

        // 2. Verify student and teacher methods
        $teacher = User::where('role', 'TEACHER')->first();
        $this->assertNotNull($teacher);
        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isAdmin());

        $student = User::where('role', 'STUDENT')->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isAdmin());
    }

    public function test_guardian_details_can_be_saved(): void
    {
        $student = User::where('role', 'STUDENT')->first();
        $this->assertNotNull($student);

        $student->update([
            'guardian_name' => 'محمود علي إبراهيم',
            'guardian_email' => 'guardian.test@example.com',
            'guardian_phone' => '+201011122233',
            'guardian_relation' => 'الأب',
        ]);

        $this->assertEquals('محمود علي إبراهيم', $student->fresh()->guardian_name);
        $this->assertEquals('guardian.test@example.com', $student->fresh()->guardian_email);
    }

    public function test_quiz_can_have_per_question_timer_and_ai_proctoring(): void
    {
        $teacher = User::where('role', 'TEACHER')->first();

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'اختبار الذكاء والسرعة مع المؤقت الذكي',
            'code' => 'TEST-' . rand(1000, 9999),
            'duration_minutes' => 15,
            'passing_score' => 60,
            'has_per_question_timer' => true,
            'per_question_seconds' => 45,
            'is_ai_proctored' => true,
            'status' => 'PUBLISHED',
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'CHOOSE',
            'question_text' => 'ما هي لغة البرمجة الأكثر استخداماً في تطوير الويب الخلفي؟',
            'points' => 5,
            'time_limit_seconds' => 45,
            'order' => 1,
        ]);

        $opt1 = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'PHP',
            'is_correct' => true,
            'order' => 0,
        ]);

        $opt2 = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'C++',
            'is_correct' => false,
            'order' => 1,
        ]);

        $this->assertTrue($quiz->has_per_question_timer);
        $this->assertTrue($quiz->is_ai_proctored);
        $this->assertEquals(45, $question->time_limit_seconds);
    }

    public function test_exam_completion_generates_certificate_and_notifies_guardian(): void
    {
        $student = User::where('role', 'STUDENT')->first();
        $teacher = User::where('role', 'TEACHER')->first();

        $student->update([
            'guardian_name' => 'ولي أمر تجريبي',
            'guardian_email' => 'parent@test.com',
            'guardian_phone' => '+201099988877',
        ]);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'اختبار نهائي تجريبي للشهادة',
            'code' => 'CERT-QZ-' . rand(100, 999),
            'duration_minutes' => 10,
            'passing_score' => 50,
            'is_ai_proctored' => true,
            'status' => 'PUBLISHED',
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'CHOOSE',
            'question_text' => 'هل 1 + 1 = 2؟',
            'points' => 10,
            'order' => 1,
        ]);

        $opt = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'نعم',
            'is_correct' => true,
            'order' => 0,
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ]);

        // Submit the attempt with correct answer and AI proctoring telemetry
        $response = $this->actingAs($student)->post(route('quizzes.submitAttempt', [$quiz, $attempt]), [
            'answers' => [
                $question->id => $opt->id,
            ],
            'proctor_violations_count' => 0,
            'proctor_status' => 'PASSED_CLEAN',
            'proctor_log' => json_encode([['time' => '10:00', 'type' => 'CLEAN', 'description' => 'جلسة نزيهة']]),
        ]);

        $response->assertRedirect(route('quizzes.results', $attempt));

        $attempt->refresh();
        $this->assertEquals('SUBMITTED', $attempt->status);
        $this->assertEquals(100, $attempt->percentage);
        $this->assertEquals('PASSED_CLEAN', $attempt->proctor_status);

        // Check Certificate created
        $cert = Certificate::where('attempt_id', $attempt->id)->first();
        $this->assertNotNull($cert, 'Certificate should be issued for passing student.');
        $this->assertEquals(100, $cert->percentage);

        // Check Guardian Notification created
        $notif = GuardianNotification::where('student_id', $student->id)
            ->where('quiz_id', $quiz->id)
            ->first();
        $this->assertNotNull($notif, 'Guardian should be notified of exam score.');
        $this->assertStringContainsString('100%', $notif->message);

        // Test Certificate Verification Page
        $verifyResponse = $this->get(route('certificates.verify', $cert->verification_code));
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee($student->name);
        $verifyResponse->assertSee($cert->verification_code);
    }

    public function test_teacher_can_send_absence_alert_to_guardian(): void
    {
        $teacher = User::where('role', 'TEACHER')->first();
        $student = User::where('role', 'STUDENT')->first();

        $student->update([
            'guardian_name' => 'والد أحمد',
            'guardian_email' => 'ahmed.parent@example.com',
            'guardian_phone' => '+201011122233',
        ]);

        $response = $this->actingAs($teacher)->post(route('guardian.alert'), [
            'student_id' => $student->id,
            'type' => 'SESSION_MISSED',
            'custom_note' => 'تغيب الطالب عن محاضرة البرمجة اليوم.',
        ]);

        $response->assertSessionHas('success');

        $notif = GuardianNotification::where('student_id', $student->id)
            ->where('type', 'SESSION_MISSED')
            ->latest()
            ->first();

        $this->assertNotNull($notif);
        $this->assertStringContainsString('غياب عن الحصة', $notif->title);
    }
}
