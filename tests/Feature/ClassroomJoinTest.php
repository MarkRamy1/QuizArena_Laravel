<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use Illuminate\Support\Facades\Hash;

class ClassroomJoinTest extends TestCase
{
    private function getOrCreateTeacher(): User
    {
        return User::firstOrCreate(
            ['email' => 'teacher_test@quizarena.com'],
            [
                'name' => 'د. محمود التجريبي',
                'password' => Hash::make('Password123!'),
                'role' => 'TEACHER',
            ]
        );
    }

    private function getOrCreateStudent(): User
    {
        return User::firstOrCreate(
            ['email' => 'student_test@quizarena.com'],
            [
                'name' => 'أحمد التجريبي',
                'password' => Hash::make('Password123!'),
                'role' => 'STUDENT',
            ]
        );
    }

    public function test_student_can_join_classroom_by_code(): void
    {
        $teacher = $this->getOrCreateTeacher();
        $student = $this->getOrCreateStudent();

        // Create a test classroom
        $classroom = Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => 'اختبار الانضمام بالكود',
            'subject' => 'تجربة',
            'code' => 'TEST-' . rand(1000, 9999),
        ]);

        $this->actingAs($student);

        // Student joins using code (testing case-insensitive)
        $response = $this->post('/classrooms/join', [
            'code' => strtolower($classroom->code),
        ]);

        $response->assertRedirect('/classrooms/' . $classroom->id);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'status' => 'ACCEPTED',
        ]);

        // Cleanup
        $classroom->delete();
    }

    public function test_student_gets_error_with_invalid_code(): void
    {
        $student = $this->getOrCreateStudent();
        $this->actingAs($student);

        $response = $this->post('/classrooms/join', [
            'code' => 'INVALID-CODE-XYZ-999',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_student_can_request_to_join_and_teacher_can_approve(): void
    {
        $teacher = $this->getOrCreateTeacher();
        $student = $this->getOrCreateStudent();

        $classroom = Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => 'فصل طلب الانضمام',
            'subject' => 'تجربة',
            'code' => 'REQ-' . rand(1000, 9999),
        ]);

        // 1. Student requests to join
        $this->actingAs($student);
        $reqResponse = $this->post('/classrooms/' . $classroom->id . '/request');
        $reqResponse->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'status' => 'PENDING',
        ]);

        // 2. Teacher approves request
        $this->actingAs($teacher);
        $approveResponse = $this->post('/classrooms/' . $classroom->id . '/members/' . $student->id, [
            'action' => 'accept',
        ]);
        $approveResponse->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'status' => 'ACCEPTED',
        ]);

        // 3. Student can now access the classroom
        $this->actingAs($student);
        $viewResponse = $this->get('/classrooms/' . $classroom->id);
        $viewResponse->assertStatus(200);

        // Cleanup
        $classroom->delete();
    }
}
