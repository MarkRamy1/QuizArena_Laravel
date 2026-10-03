<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\AttemptAnswer;
use App\Models\Certificate;
use App\Models\GuardianNotification;
use App\Models\Notification;
use App\Models\User;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            $quizzes = Quiz::where('teacher_id', $user->id)
                ->with(['classroom'])
                ->withCount(['questions', 'attempts'])
                ->latest()
                ->get();
            return view('quizzes.index', compact('quizzes'));
        }

        // Student quizzes
        $enrolledClassroomIds = $user->enrolledClassrooms()->pluck('classrooms.id');
        $quizzes = Quiz::where(function ($q) use ($enrolledClassroomIds) {
            $q->whereIn('classroom_id', $enrolledClassroomIds)->orWhereNull('classroom_id');
        })->with(['classroom', 'teacher'])
          ->withCount('questions')
          ->latest()
          ->get();

        $myAttempts = $user->quizAttempts()->with('quiz')->latest()->get()->groupBy('quiz_id');

        return view('quizzes.index', compact('quizzes', 'myAttempts'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user->isTeacher()) {
            abort(403);
        }

        $classroomId = $request->query('classroom_id');
        $classrooms = Classroom::where('teacher_id', $user->id)->get();

        return view('quizzes.create', compact('classrooms', 'classroomId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isTeacher()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|integer|min:0|max:100',
            'has_per_question_timer' => 'nullable|boolean',
            'per_question_seconds' => 'nullable|integer|min:0|max:600',
            'is_ai_proctored' => 'nullable|boolean',
            'pdf_exam' => 'nullable|file|mimes:pdf|max:20480',
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|in:CHOOSE,TRUE_FALSE,ESSAY,COMPLETE,MATCH',
            'questions.*.question_text' => 'required|string',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.time_limit_seconds' => 'nullable|integer|min:0|max:600',
        ]);

        do {
            $code = 'QZ-' . strtoupper(Str::random(6));
        } while (Quiz::where('code', $code)->exists());

        $pdfPath = null;
        $pdfName = null;
        if ($request->hasFile('pdf_exam')) {
            $pdfFile = $request->file('pdf_exam');
            $pdfName = $pdfFile->getClientOriginalName();
            $pdfPath = $pdfFile->store('quiz_pdfs', 'public');
        }

        $hasPerQuestionTimer = $request->boolean('has_per_question_timer');
        $perQuestionSeconds = (int) $request->input('per_question_seconds', 0);
        $isAiProctored = $request->has('is_ai_proctored') ? $request->boolean('is_ai_proctored') : true;

        $quiz = Quiz::create([
            'classroom_id' => $validated['classroom_id'] ?? null,
            'teacher_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'code' => $code,
            'duration_minutes' => $validated['duration_minutes'],
            'passing_score' => $validated['passing_score'],
            'has_per_question_timer' => $hasPerQuestionTimer,
            'per_question_seconds' => $perQuestionSeconds,
            'is_ai_proctored' => $isAiProctored,
            'pdf_exam_path' => $pdfPath,
            'pdf_exam_name' => $pdfName,
            'status' => 'PUBLISHED',
        ]);

        foreach ($validated['questions'] as $index => $qData) {
            $questionTimeLimit = !empty($qData['time_limit_seconds']) 
                ? (int)$qData['time_limit_seconds'] 
                : ($hasPerQuestionTimer ? $perQuestionSeconds : 0);

            $question = Question::create([
                'quiz_id' => $quiz->id,
                'type' => $qData['type'],
                'question_text' => $qData['question_text'],
                'points' => $qData['points'] ?? 1,
                'time_limit_seconds' => $questionTimeLimit,
                'order' => $index + 1,
                'explanation' => $qData['explanation'] ?? null,
                'model_answer' => $qData['model_answer'] ?? null,
                'match_pairs' => ($qData['type'] === 'MATCH' && isset($qData['match_pairs'])) ? $qData['match_pairs'] : null,
            ]);

            // Save options for CHOOSE or TRUE_FALSE
            if (in_array($qData['type'], ['CHOOSE', 'TRUE_FALSE']) && !empty($qData['options'])) {
                foreach ($qData['options'] as $optIdx => $optText) {
                    if (trim($optText) === '') continue;
                    $isCorrect = (isset($qData['correct_option']) && (int)$qData['correct_option'] === $optIdx);
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optText,
                        'is_correct' => $isCorrect,
                        'order' => $optIdx,
                    ]);
                }
            }
        }

        // Requirement 5: Test code is sent to the students immediately
        $assignedStudents = User::where('teacher_id', $user->id)->get();
        foreach ($assignedStudents as $assignedStudent) {
            Notification::create([
                'user_id' => $assignedStudent->id,
                'title' => "📢 اختبار جديد من معلمك: {$quiz->title}",
                'message' => "أضاف المعلم {$user->name} اختباراً جديداً بعنوان '{$quiz->title}'. كود الاختبار للدخول: [ {$quiz->code} ] (المدة: {$quiz->duration_minutes} دقيقة).",
                'type' => 'QUIZ',
                'url' => route('quizzes.show', $quiz),
                'is_read' => false,
            ]);
        }

        if ($quiz->classroom_id) {
            return redirect()->route('classrooms.show', $quiz->classroom_id)->with('success', __('app.create_quiz') . ' - ' . $quiz->title);
        }

        return redirect()->route('quizzes.show', $quiz)->with('success', __('app.create_quiz') . ' - ' . $quiz->title);
    }

    public function show(Quiz $quiz)
    {
        $user = Auth::user();
        $quiz->load(['questions.options', 'classroom', 'teacher']);

        $isTeacher = ($quiz->teacher_id === $user->id);
        $attempts = collect();
        $myAttempts = collect();

        if ($isTeacher) {
            $attempts = $quiz->attempts()->with(['student', 'certificate'])->latest()->get();
        } else {
            $myAttempts = $quiz->attempts()->where('student_id', $user->id)->with('certificate')->latest()->get();
        }

        return view('quizzes.show', compact('quiz', 'isTeacher', 'attempts', 'myAttempts'));
    }

    public function start(Quiz $quiz)
    {
        $user = Auth::user();

        // Check if student is member of classroom if restricted
        if ($quiz->classroom_id) {
            $isMember = $quiz->classroom->students()->where('users.id', $user->id)->exists();
            if (!$isMember && !$user->isTeacher()) {
                abort(403, 'يجب الانضمام للفصل الدراسي أولاً لبدء هذا الاختبار.');
            }
        }

        // Check for active in-progress attempt or create a new one
        $attempt = QuizAttempt::firstOrCreate(
            [
                'quiz_id' => $quiz->id,
                'student_id' => $user->id,
                'status' => 'IN_PROGRESS',
            ],
            [
                'started_at' => now(),
            ]
        );

        return redirect()->route('quizzes.take', [$quiz, $attempt]);
    }

    public function take(Quiz $quiz, QuizAttempt $attempt)
    {
        $user = Auth::user();

        if ($attempt->student_id !== $user->id) {
            abort(403);
        }

        if ($attempt->status !== 'IN_PROGRESS') {
            return redirect()->route('quizzes.results', $attempt);
        }

        $quiz->load(['questions.options']);

        return view('quizzes.take', compact('quiz', 'attempt'));
    }

    public function submitAttempt(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        $user = Auth::user();

        if ($attempt->student_id !== $user->id) {
            abort(403);
        }

        if ($attempt->status !== 'IN_PROGRESS') {
            return redirect()->route('quizzes.results', $attempt);
        }

        $answers = $request->input('answers', []);
        $totalScore = 0;
        $maxScore = 0;

        foreach ($quiz->questions as $question) {
            $points = $question->points;
            $maxScore += $points;
            $awarded = 0;
            $isCorrect = false;
            $selectedOptionId = null;
            $studentAnswer = null;

            if ($question->type === 'CHOOSE' || $question->type === 'TRUE_FALSE') {
                $selectedOptionId = $answers[$question->id] ?? null;
                if ($selectedOptionId) {
                    $opt = QuestionOption::find($selectedOptionId);
                    if ($opt && $opt->is_correct) {
                        $isCorrect = true;
                        $awarded = $points;
                    }
                }
            } elseif ($question->type === 'COMPLETE') {
                $studentAnswer = trim($answers[$question->id] ?? '');
                if (strcasecmp($studentAnswer, trim($question->model_answer ?? '')) === 0) {
                    $isCorrect = true;
                    $awarded = $points;
                }
            } elseif ($question->type === 'ESSAY') {
                $studentAnswer = trim($answers[$question->id] ?? '');
                $isCorrect = !empty($studentAnswer);
                $awarded = $isCorrect ? $points : 0;
            } elseif ($question->type === 'MATCH') {
                $studentPairs = $answers[$question->id] ?? [];
                $studentAnswer = json_encode($studentPairs, JSON_UNESCAPED_UNICODE);

                $matchPairs = $question->match_pairs ?? [];
                $correctCount = 0;
                $totalPairs = count($matchPairs);

                if ($totalPairs > 0 && is_array($studentPairs)) {
                    foreach ($matchPairs as $idx => $pair) {
                        $expectedRight = trim($pair['right'] ?? '');
                        $givenRight = trim($studentPairs[$idx] ?? '');
                        if (strcasecmp($expectedRight, $givenRight) === 0) {
                            $correctCount++;
                        }
                    }
                    if ($correctCount === $totalPairs) {
                        $isCorrect = true;
                        $awarded = $points;
                    } else {
                        $awarded = (int) round(($correctCount / $totalPairs) * $points);
                        $isCorrect = ($correctCount > 0);
                    }
                }
            }

            $totalScore += $awarded;

            AttemptAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                [
                    'selected_option_id' => $selectedOptionId,
                    'student_answer' => $studentAnswer,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $awarded,
                ]
            );
        }

        $percentage = $maxScore > 0 ? (int) round(($totalScore / $maxScore) * 100) : 0;

        $proctorViolations = (int) $request->input('proctor_violations_count', 0);
        $proctorStatus = $request->input('proctor_status', ($proctorViolations > 3 ? 'FLAGGED_SUSPICIOUS' : 'PASSED_CLEAN'));
        $proctorLog = $request->input('proctor_log');

        $attempt->update([
            'status' => 'SUBMITTED',
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'proctor_violations_count' => $proctorViolations,
            'proctor_status' => $proctorStatus,
            'proctor_log' => $proctorLog,
            'submitted_at' => now(),
        ]);

        // Issue Printable Digital Certificate if passed
        $certificate = null;
        if ($percentage >= $quiz->passing_score) {
            $certificate = Certificate::firstOrCreate(
                ['attempt_id' => $attempt->id],
                [
                    'student_id' => $user->id,
                    'quiz_id' => $quiz->id,
                    'teacher_id' => $quiz->teacher_id,
                    'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
                    'verification_code' => 'VRF-' . strtoupper(Str::random(8)),
                    'percentage' => $percentage,
                    'issued_at' => now(),
                ]
            );
        }

        // Automatic Notification to Guardian regarding exam score
        if (!empty($user->guardian_email) || !empty($user->guardian_phone)) {
            $isPass = ($percentage >= $quiz->passing_score);
            $statusText = $isPass ? "ناجح باقتدار وامتياز 🏆" : "لم يحقق درجة النجاح المطلوبة ({$quiz->passing_score}%) ويحتاج للمتابعة والدعم الأكاديمي ⚠️";

            GuardianNotification::create([
                'student_id' => $user->id,
                'classroom_id' => $quiz->classroom_id,
                'quiz_id' => $quiz->id,
                'teacher_id' => $quiz->teacher_id,
                'type' => 'EXAM_SCORE',
                'guardian_name' => $user->guardian_name,
                'guardian_email' => $user->guardian_email,
                'guardian_phone' => $user->guardian_phone,
                'title' => 'تقرير نتيجة اختبار: ' . $quiz->title,
                'message' => "نحيطكم علماً بأن الطالب ({$user->name}) قد أتم اختبار [{$quiz->title}] بتاريخ " . now()->format('Y-m-d H:i') . "، وحصل على مجموع {$totalScore} من {$maxScore} (نسبة النجاح: {$percentage}%).\nالحالة التقييمية: {$statusText}" . ($certificate ? "\nتم منح الطالب شهادة إنجاز رقمية برقم: {$certificate->verification_code}" : ""),
                'status' => 'SENT',
                'sent_at' => now(),
            ]);
        }

        return redirect()->route('quizzes.results', $attempt)->with('success', __('app.quiz_results'));
    }

    public function results(QuizAttempt $attempt)
    {
        $user = Auth::user();
        $isOwner = ($attempt->student_id === $user->id);
        $isTeacher = ($attempt->quiz->teacher_id === $user->id);

        if (!$isOwner && !$isTeacher) {
            abort(403);
        }

        $attempt->load(['quiz.questions.options', 'answers.selectedOption', 'student', 'certificate']);

        $guardianAlerts = GuardianNotification::where('student_id', $attempt->student_id)
            ->where('quiz_id', $attempt->quiz_id)
            ->latest()
            ->get();

        return view('quizzes.results', compact('attempt', 'isTeacher', 'isOwner', 'guardianAlerts'));
    }

    public function downloadPdf(Quiz $quiz)
    {
        if (!$quiz->pdf_exam_path || !Storage::disk('public')->exists($quiz->pdf_exam_path)) {
            abort(404, 'ملف الاختبار PDF غير موجود على الخادم.');
        }

        return Storage::disk('public')->download($quiz->pdf_exam_path, $quiz->pdf_exam_name ?? ($quiz->title . '.pdf'));
    }

    public function exportResults(Quiz $quiz)
    {
        abort_unless(Auth::user()->isTeacher() || Auth::user()->isAdmin(), 403);

        $attempts = $quiz->attempts()->with('student')->latest()->get();
        $fileName = 'quiz_results_' . $quiz->code . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($attempts, $quiz) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel Arabic support
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['اسم الطالب', 'البريد الإلكتروني', 'تاريخ التسليم', 'الدرجة المحققة', 'الدرجة الكلية', 'النسبة المئوية', 'مؤشر النزاهة', 'حالة النتيجة']);

            foreach ($attempts as $att) {
                fputcsv($handle, [
                    $att->student->name,
                    $att->student->email,
                    $att->submitted_at ? $att->submitted_at->format('Y-m-d H:i') : '-',
                    $att->score,
                    $att->max_score,
                    $att->percentage . '%',
                    $att->proctor_violations_count > 0 ? "⚠️ {$att->proctor_violations_count} مخالفات" : 'نظيف ✓',
                    $att->percentage >= $quiz->passing_score ? 'ناجح' : 'راسب',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analytics()
    {
        $user = Auth::user();
        abort_unless($user->isTeacher() || $user->isAdmin(), 403);

        $quizzes = Quiz::where('teacher_id', $user->id)->withCount(['questions', 'attempts'])->get();
        $allAttempts = QuizAttempt::whereIn('quiz_id', $quizzes->pluck('id'))->where('status', 'GRADED')->get();

        $avgScore = $allAttempts->count() > 0 ? (int) round($allAttempts->avg('percentage')) : 80;
        $passCount = $allAttempts->where('percentage', '>=', 60)->count();
        $passRate = $allAttempts->count() > 0 ? (int) round(($passCount / $allAttempts->count()) * 100) : 85;

        // Hardest questions
        $hardestQuestions = Question::whereIn('quiz_id', $quizzes->pluck('id'))
            ->withCount(['answers as incorrect_count' => function ($q) {
                $q->where('is_correct', false);
            }])
            ->orderByDesc('incorrect_count')
            ->take(5)
            ->get();

        return view('quizzes.analytics', compact('quizzes', 'avgScore', 'passRate', 'allAttempts', 'hardestQuestions'));
    }
}
