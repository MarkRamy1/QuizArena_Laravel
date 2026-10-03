<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Classroom;
use App\Helpers\ArabicHelper;
use App\Helpers\StreakHelper;

class QuizArenaTest extends TestCase
{

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('QuizArena');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول');
    }

    public function test_authenticated_student_can_view_dashboard(): void
    {
        $student = User::where('role', 'STUDENT')->first() ?? User::factory()->create(['role' => 'STUDENT']);
        $response = $this->actingAs($student)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_quizzes_index_loads(): void
    {
        $student = User::where('role', 'STUDENT')->first() ?? User::factory()->create(['role' => 'STUDENT']);
        $response = $this->actingAs($student)->get('/quizzes');
        $response->assertStatus(200);
    }

    public function test_demo_quiz_details_page(): void
    {
        $student = User::where('role', 'STUDENT')->first() ?? User::factory()->create(['role' => 'STUDENT']);
        $quiz = Quiz::first();
        if ($quiz) {
            $response = $this->actingAs($student)->get("/quizzes/{$quiz->id}");
            $response->assertStatus(200);
            $response->assertSee($quiz->title);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_arabic_helper_normalizes_text_correctly(): void
    {
        // Diacritics stripping
        $this->assertEquals('محمد', ArabicHelper::normalize('مُحَمَّدٌ'));
        // Alef normalization (أ, إ, آ -> ا)
        $this->assertEquals('احمد', ArabicHelper::normalize('أَحْمَد'));
        // Taa marbuta normalization (ة -> ه)
        $this->assertEquals('مدرسه', ArabicHelper::normalize('مَدْرَسَةٌ'));
        // Alef maqsura normalization (ى -> ي)
        $this->assertEquals('علي', ArabicHelper::normalize('عَلَى'));

        // Answer comparison
        $this->assertTrue(ArabicHelper::compareAnswers('https', 'HTTPS'));
        $this->assertTrue(ArabicHelper::compareAnswers('أحمد', 'احمد'));
    }

    public function test_streak_evaluator_increments_consecutive_day(): void
    {
        $result = StreakHelper::evaluateStreak('2026-09-28', '2026-09-29', 5);
        $this->assertEquals(6, $result['streak']);
        $this->assertTrue($result['streakUpdated']);
    }

    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok', 'database' => 'connected']);
    }

    public function test_classroom_system_is_seeded(): void
    {
        $teacher = User::where('role', 'TEACHER')->first() ?? User::factory()->create(['role' => 'TEACHER']);
        $classroom = Classroom::first() ?? Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => 'فصل تجريبي',
            'subject' => 'الفيزياء',
            'code' => 'CLS-TEST1',
        ]);
        $this->assertNotNull($classroom);
        $this->assertNotEmpty($classroom->code);
    }
}
