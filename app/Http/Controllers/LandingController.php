<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Certificate;

class LandingController extends Controller
{
    public function index()
    {
        $stats = [
            'students_count' => User::where('role', 'STUDENT')->count() + 5400,
            'teachers_count' => User::where('role', 'TEACHER')->count() + 180,
            'courses_count' => Course::where('status', 'PUBLISHED')->count() + 120,
            'quizzes_count' => Quiz::where('status', 'PUBLISHED')->count() + 450,
            'certificates_count' => Certificate::count() + 3800,
        ];

        $featuredCourses = Course::where('status', 'PUBLISHED')
            ->with(['teacher', 'lessons'])
            ->latest()
            ->take(3)
            ->get();

        $featuredQuizzes = Quiz::where('status', 'PUBLISHED')
            ->with(['teacher', 'classroom'])
            ->latest()
            ->take(3)
            ->get();

        return view('landing', compact('stats', 'featuredCourses', 'featuredQuizzes'));
    }

    public function joinQuiz(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));
        if (empty($code)) {
            return back()->with('error', 'يرجى إدخال كود الاختبار أو رمز الـ PIN.');
        }

        // Check if code is a regular quiz code
        $quiz = Quiz::where('code', $code)->first();
        if ($quiz) {
            return redirect()->route('quizzes.show', $quiz->id);
        }

        // Check if code is a live quiz session pin
        $liveSession = \App\Models\LiveQuizSession::where('pin_code', $code)->where('status', '!=', 'FINISHED')->first();
        if ($liveSession) {
            return redirect()->route('live-quiz.room', $liveSession->id);
        }

        // Check if it matches a classroom code
        $classroom = \App\Models\Classroom::where('code', $code)->first();
        if ($classroom) {
            return redirect()->route('classrooms.show', $classroom->id);
        }

        return back()->with('error', "لم يتم العثور على اختبار أو فصل بالكود: {$code}. تأكد من صحة الكود والمحاولة مجدداً.");
    }
}
