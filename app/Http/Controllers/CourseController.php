<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Certificate;
use App\Models\AuditLog;
use App\Models\Notification;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['teacher', 'lessons'])->where('status', 'PUBLISHED');

        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $courses = $query->latest()->paginate(9);
        $subjects = Course::distinct()->pluck('subject')->filter();

        return view('courses.index', compact('courses', 'subjects'));
    }

    public function show(Course $course)
    {
        $course->load(['teacher', 'lessons', 'quizzes']);
        $user = Auth::user();
        $progress = $course->getProgressForUser($user);

        return view('courses.show', compact('course', 'progress'));
    }

    public function lesson(Course $course, Lesson $lesson)
    {
        $user = Auth::user();
        $lessons = $course->lessons()->orderBy('order')->get();
        $isCompleted = $lesson->isCompletedBy($user);

        // Find next and previous lessons
        $currentIndex = $lessons->search(fn($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex !== false && $currentIndex < $lessons->count() - 1) ? $lessons[$currentIndex + 1] : null;

        $progress = $course->getProgressForUser($user);

        return view('courses.lesson', compact('course', 'lesson', 'lessons', 'isCompleted', 'prevLesson', 'nextLesson', 'progress'));
    }

    public function completeLesson(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        $completion = LessonCompletion::firstOrCreate([
            'student_id' => $user->id,
            'lesson_id' => $lesson->id,
        ], [
            'completed_at' => now(),
        ]);

        // Award 30 XP
        $user->addXp(30);

        $course = $lesson->course;
        $progress = $course->getProgressForUser($user);

        // If completed 100%, issue certificate if an associated quiz and attempt exist
        if ($progress >= 100) {
            $associatedQuiz = $course->quizzes()->first();
            $attempt = $associatedQuiz ? QuizAttempt::where('quiz_id', $associatedQuiz->id)->where('student_id', $user->id)->first() : null;

            if ($associatedQuiz && $attempt) {
                $certCode = 'CERT-COURSE-' . strtoupper(substr(md5($user->id . '-' . $course->id . '-' . time()), 0, 8));
                Certificate::firstOrCreate([
                    'student_id' => $user->id,
                    'quiz_id' => $associatedQuiz->id,
                    'attempt_id' => $attempt->id,
                ], [
                    'teacher_id' => $course->teacher_id,
                    'certificate_number' => 'QA-' . date('Y') . '-' . rand(1000, 9999),
                    'verification_code' => $certCode,
                    'percentage' => 100,
                    'issued_at' => now(),
                ]);

                Notification::send(
                    $user->id,
                    'تهانينا! حصلت على شهادة الدورة 📜',
                    "لقد أتممت دورة {$course->title} بنسبة 100% وتم إصدار شهادة الإتمام بنجاح!",
                    'CERTIFICATE',
                    route('certificates.index')
                );
            }
        }

        AuditLog::log('LESSON_COMPLETED', "أكمل الطالب {$user->name} دراسة: {$lesson->title}", $user->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'progress' => $progress,
                'message' => 'تم تسجيل إتمام الدرس وإضافة 30 XP!',
            ]);
        }

        return back()->with('success', 'رائع! تم تسجيل إتمام الدرس وإضافة 30 XP إلى رصيدك 🚀');
    }

    // Teacher Management Actions
    public function create()
    {
        abort_unless(Auth::user()->isTeacher() || Auth::user()->isAdmin(), 403);
        $classrooms = Auth::user()->createdClassrooms;
        return view('courses.create', compact('classrooms'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isTeacher() || Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'subject' => 'required|string|max:100',
            'grade' => 'nullable|string|max:100',
            'difficulty' => 'required|in:EASY,MEDIUM,HARD',
            'description' => 'required|string',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'thumbnail' => 'nullable|url',
        ]);

        $course = Course::create([
            'teacher_id' => Auth::id(),
            'classroom_id' => $validated['classroom_id'] ?? null,
            'title' => $validated['title'],
            'subject' => $validated['subject'],
            'grade' => $validated['grade'] ?? null,
            'difficulty' => $validated['difficulty'],
            'description' => $validated['description'],
            'thumbnail' => $validated['thumbnail'] ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80',
            'status' => 'PUBLISHED',
        ]);

        AuditLog::log('COURSE_CREATED', "أنشأ المعلم " . Auth::user()->name . " دورة تعليمية جديدة: {$course->title}", Auth::id());

        return redirect()->route('courses.show', $course->id)->with('success', 'تم إنشاء الدورة التعليمية بنجاح! يمكنك الآن إضافة الدروس.');
    }

    public function storeLesson(Request $request, Course $course)
    {
        abort_unless(Auth::user()->id === $course->teacher_id || Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'video_url' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'is_free' => 'boolean',
        ]);

        $order = $course->lessons()->max('order') + 1;

        $lesson = $course->lessons()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'order' => $order,
            'is_free' => $request->boolean('is_free', true),
        ]);

        return back()->with('success', "تم إضافة الدرس: {$lesson->title} بنجاح!");
    }
}
