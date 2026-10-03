<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\StudyTask;
use App\Models\LiveQuizSession;
use App\Models\Notification;
use App\Models\FeedbackReport;
use App\Models\AuditLog;

class QuizArenaPlatformTest extends TestCase
{
    protected User $admin;
    protected User $teacher;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'ADMIN')->first() ?? User::factory()->create(['role' => 'ADMIN']);
        $this->teacher = User::where('role', 'TEACHER')->first() ?? User::factory()->create(['role' => 'TEACHER']);
        $this->student = User::where('role', 'STUDENT')->first() ?? User::factory()->create(['role' => 'STUDENT']);
    }

    public function test_landing_page_renders_with_metrics_and_quick_links(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('QuizArena');
        $response->assertSee('انضم إلى اختبار');
    }

    public function test_student_dashboard_has_personalized_learning_and_gamification(): void
    {
        $response = $this->actingAs($this->student)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('مسار التعلم المخصص والتشخيص الذكي');
        $response->assertSee('المهام اليومية');
        $response->assertSee('المستوى الأكاديمي');
    }

    public function test_teacher_dashboard_has_analytics_and_hardest_questions(): void
    {
        $response = $this->actingAs($this->teacher)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('كود المعلم الخاص بك');
        $response->assertSee('أصعب الأسئلة');
        $response->assertSee('الطلاب المحتاجون للمساعدة');
    }

    public function test_admin_dashboard_and_governance(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم والإدارة المركزية');
        $response->assertSee('إدارة المستخدمين');

        $usersResponse = $this->actingAs($this->admin)->get('/admin/users');
        $usersResponse->assertStatus(200);
        $usersResponse->assertSee($this->teacher->name);
        $usersResponse->assertSee($this->student->name);

        $logsResponse = $this->actingAs($this->admin)->get('/admin/audit-logs');
        $logsResponse->assertStatus(200);
        $logsResponse->assertSee('سجل التدقيق');
    }

    public function test_course_and_lesson_flow_with_xp(): void
    {
        $course = Course::with('lessons')->first();
        if (!$course) {
            $course = Course::create([
                'title' => 'مقدمة في البرمجة الحديثة ' . rand(1000, 9999),
                'description' => 'دورة أساسية لتعلم المفاهيم البرمجية',
                'subject' => 'علوم الحاسب',
                'teacher_id' => $this->teacher->id,
                'status' => 'PUBLISHED',
                'level' => 'BEGINNER',
            ]);
            Lesson::create([
                'course_id' => $course->id,
                'title' => 'الدرس الأول: المتغيرات والأنواع',
                'content' => 'محتوى الدرس التفاعلي',
                'order' => 1,
                'xp_reward' => 20,
            ]);
            $course->refresh();
        }

        // Course Catalog
        $indexResponse = $this->actingAs($this->student)->get('/courses');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($course->title);

        // Course Show
        $showResponse = $this->actingAs($this->student)->get("/courses/{$course->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($course->title);

        // Lesson Player
        $lesson = $course->lessons->first();
        if ($lesson) {
            $lessonResponse = $this->actingAs($this->student)->get("/courses/{$course->id}/lessons/{$lesson->id}");
            $lessonResponse->assertStatus(200);
            $lessonResponse->assertSee($lesson->title);

            // Complete lesson and earn XP
            $completeResponse = $this->actingAs($this->student)->post("/lessons/{$lesson->id}/complete");
            $completeResponse->assertRedirect();
            $this->assertDatabaseHas('lesson_completions', [
                'student_id' => $this->student->id,
                'lesson_id' => $lesson->id,
            ]);
        }
    }

    public function test_study_planner_crud(): void
    {
        $response = $this->actingAs($this->student)->get('/planner');
        $response->assertStatus(200);
        $response->assertSee('خطة المذاكرة الأسبوعية');

        // Create Task
        $topic = 'مراجعة قوانين نيوتن والجاذبية ' . rand(10000, 99999);
        $createResponse = $this->actingAs($this->student)->post('/planner', [
            'subject' => 'الفيزياء الحديثة',
            'topic' => $topic,
            'day_of_week' => 'Monday',
            'duration_minutes' => 45,
        ]);
        $createResponse->assertRedirect();

        $task = StudyTask::where('student_id', $this->student->id)
            ->where('topic', $topic)
            ->first();
        $this->assertNotNull($task);
        $this->assertFalse($task->is_completed);

        // Toggle Task
        $toggleResponse = $this->actingAs($this->student)->post("/planner/{$task->id}/toggle");
        $toggleResponse->assertRedirect();
        $task->refresh();
        $this->assertTrue($task->is_completed);
    }

    public function test_global_search_across_entities(): void
    {
        $response = $this->actingAs($this->student)->get('/search?q=البرمجة');
        $response->assertStatus(200);
        $response->assertSee('محرك البحث العام الموحد');
    }

    public function test_notifications_center(): void
    {
        $notif = Notification::create([
            'user_id' => $this->student->id,
            'title' => 'اختبار تجريبي جديد',
            'message' => 'أضاف معلمك اختباراً جديداً للفيزياء',
            'type' => 'QUIZ',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->student)->get('/notifications');
        $response->assertStatus(200);
        $response->assertSee('اختبار تجريبي جديد');

        // Mark Read
        $readResponse = $this->actingAs($this->student)->post("/notifications/{$notif->id}/read");
        $readResponse->assertRedirect();
        $notif->refresh();
        $this->assertTrue($notif->is_read);
    }

    public function test_feedback_and_reports_system(): void
    {
        $response = $this->actingAs($this->student)->post('/feedback', [
            'type' => 'QUESTION',
            'title' => 'سؤال رقم 4 يحتمل إجابتين صحيحتين',
            'description' => 'يرجى مراجعة صياغة السؤال وخيارات الإجابة في اختبار الكيمياء.',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('feedback_reports', [
            'user_id' => $this->student->id,
            'title' => 'سؤال رقم 4 يحتمل إجابتين صحيحتين',
            'status' => 'PENDING',
        ]);
    }

    public function test_live_quiz_session(): void
    {
        $quiz = Quiz::first();
        if (!$quiz) {
            $quiz = Quiz::create([
                'title' => 'اختبار حي تجريبي ' . rand(1000, 9999),
                'code' => 'LIVE-' . rand(100, 999),
                'teacher_id' => $this->teacher->id,
                'status' => 'PUBLISHED',
                'duration_minutes' => 15,
                'passing_score' => 60,
            ]);
        }

        // Teacher starts live quiz
        $startResponse = $this->actingAs($this->teacher)->post("/live-quiz/start/{$quiz->id}");
        $startResponse->assertRedirect();

        $session = LiveQuizSession::where('quiz_id', $quiz->id)->latest()->first();
        $this->assertNotNull($session);
        $this->assertNotEmpty($session->pin_code);

        // Student joins live quiz
        $joinResponse = $this->actingAs($this->student)->post('/live-quiz/join', [
            'pin_code' => $session->pin_code,
        ]);
        $joinResponse->assertRedirect(route('live-quiz.room', $session));

        // Host screen
        $hostResponse = $this->actingAs($this->teacher)->get("/live-quiz/host/{$session->id}");
        $hostResponse->assertStatus(200);
        $hostResponse->assertSee($session->pin_code);
    }

    public function test_teacher_code_generation_and_student_linking(): void
    {
        $code = $this->teacher->generateTeacherCode();
        $this->assertNotEmpty($code);
        $this->assertStringStartsWith('TCH-', $code);

        // Student links to teacher
        $response = $this->actingAs($this->student)->post('/student/link-teacher', [
            'teacher_code' => $code,
        ]);
        $response->assertRedirect();
        $this->student->refresh();
        $this->assertEquals($this->teacher->id, $this->student->teacher_id);
    }

    public function test_admin_can_view_teachers_and_assigned_students(): void
    {
        $this->teacher->generateTeacherCode();
        $this->student->update(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAs($this->admin)->get('/admin/teachers');
        $response->assertStatus(200);
        $response->assertSee($this->teacher->name);
        $response->assertSee($this->teacher->teacher_code);
        $response->assertSee($this->student->name);
    }

    public function test_ai_tutor_answers_academic_question(): void
    {
        $response = $this->actingAs($this->student)->postJson('/ai/chat', [
            'message' => 'حل المعادلة: 2س + 6 = 18',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertStringContainsString('س = 6', $response->json('reply'));
    }

    public function test_ai_tutor_and_teacher_assistant(): void
    {
        // Student AI Tutor
        $tutorResponse = $this->actingAs($this->student)->get('/ai/tutor');
        $tutorResponse->assertStatus(200);
        $tutorResponse->assertSee('المعلم الذكي');

        // Teacher AI Assistant
        $teacherAiResponse = $this->actingAs($this->teacher)->get('/ai/teacher');
        $teacherAiResponse->assertStatus(200);
        $teacherAiResponse->assertSee('مساعد المعلم الذكي');

        // Teacher AI Generate Questions API
        $genResponse = $this->actingAs($this->teacher)->postJson('/ai/teacher/generate-questions', [
            'topic' => 'قوانين الحركة لنيوتن',
            'difficulty' => 'MEDIUM',
            'count' => 2,
        ]);
        $genResponse->assertStatus(200);
        $genResponse->assertJsonStructure(['questions']);

        // Admin AI Insights
        $adminAiResponse = $this->actingAs($this->admin)->get('/ai/admin');
        $adminAiResponse->assertStatus(200);
        $adminAiResponse->assertSee('مساعد المشرف العام الذكي');
    }
}
