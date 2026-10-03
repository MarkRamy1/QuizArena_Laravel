<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Certificate;
use App\Models\Resource;
use App\Models\GamificationProfile;
use App\Models\Mission;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiUsage;
use App\Helpers\ArabicHelper;
use App\Helpers\CryptoHelper;
use App\Helpers\StreakHelper;

class ApiController extends Controller
{
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'framework' => 'Laravel ' . app()->version(),
            'timestamp' => now()->toIso8601String(),
            'database' => 'connected',
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'بيانات الاعتماد غير صحيحة'], 401);
        }

        Auth::login($user);
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'xp' => $user->gamificationProfile?->xp ?? 0,
                'level' => $user->gamificationProfile?->level ?? 1,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'gamification' => $user->gamificationProfile,
            ],
        ]);
    }

    public function quizzes(Request $request)
    {
        $query = Quiz::where('status', 'PUBLISHED')->with(['subject', 'teacher']);
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }
        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function quizByCode($code)
    {
        $quiz = Quiz::where('code', strtoupper($code))->with(['subject', 'teacher', 'questions.choices'])->first();
        if (!$quiz) {
            return response()->json(['success' => false, 'message' => 'الاختبار غير موجود'], 404);
        }
        return response()->json(['success' => true, 'data' => $quiz]);
    }

    public function quizDetails($id)
    {
        $quiz = Quiz::with(['subject', 'teacher', 'questions.choices'])->find($id);
        if (!$quiz) {
            return response()->json(['success' => false, 'message' => 'الاختبار غير موجود'], 404);
        }
        return response()->json(['success' => true, 'data' => $quiz]);
    }

    public function startAttempt($quizId)
    {
        $user = Auth::user() ?? User::where('role', 'STUDENT')->first();
        $quiz = Quiz::findOrFail($quizId);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
            'expires_at' => now()->addMinutes($quiz->duration_minutes),
        ]);

        return response()->json(['success' => true, 'data' => $attempt]);
    }

    public function getAttempt($id)
    {
        $attempt = QuizAttempt::with(['quiz.questions.choices', 'answers'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $attempt]);
    }

    public function saveAnswer(Request $request, $id)
    {
        $attempt = QuizAttempt::findOrFail($id);
        $answer = AttemptAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $request->input('question_id')],
            [
                'choice_id' => $request->input('choice_id'),
                'text_answer' => $request->input('text_answer'),
                'is_marked_for_review' => (bool) $request->input('is_marked_for_review'),
                'answered_at' => now(),
            ]
        );
        return response()->json(['success' => true, 'data' => $answer]);
    }

    public function submitAttempt($id)
    {
        $attempt = QuizAttempt::with(['quiz.questions.choices', 'answers', 'student.gamificationProfile'])->findOrFail($id);

        if ($attempt->status === 'SUBMITTED') {
            return response()->json(['success' => true, 'data' => $attempt]);
        }

        $totalPoints = 0;
        $maxPoints = 0;
        $answersMap = $attempt->answers->keyBy('question_id');

        foreach ($attempt->quiz->questions as $q) {
            $maxPoints += $q->points;
            $ans = $answersMap->get($q->id);
            $isCorrect = false;
            $pts = 0;

            if ($ans) {
                if ($q->type === 'MCQ_SINGLE') {
                    $choice = $q->choices->firstWhere('id', $ans->choice_id);
                    if ($choice && $choice->is_correct) {
                        $isCorrect = true;
                        $pts = $q->points;
                    }
                } elseif ($q->type === 'SHORT_TEXT' && $ans->text_answer && $q->model_answer) {
                    if (ArabicHelper::compareAnswers($ans->text_answer, $q->model_answer)) {
                        $isCorrect = true;
                        $pts = $q->points;
                    }
                }
                $ans->update(['is_correct' => $isCorrect, 'points_awarded' => $pts]);
            }
            $totalPoints += $pts;
        }

        $percentage = $maxPoints > 0 ? (int) round(($totalPoints / $maxPoints) * 100) : 0;
        $attempt->update([
            'status' => 'SUBMITTED',
            'grading_status' => 'AUTO_GRADED',
            'score' => $totalPoints,
            'max_score' => $maxPoints,
            'percentage' => $percentage,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسليم الاختبار وتصحيحه بنجاح',
            'data' => [
                'attempt_id' => $attempt->id,
                'score' => $totalPoints,
                'max_score' => $maxPoints,
                'percentage' => $percentage,
                'passed' => $percentage >= $attempt->quiz->passing_score,
            ],
        ]);
    }

    public function verifyCertificate($code)
    {
        $cert = Certificate::where('verification_code', strtoupper($code))->with(['student', 'quiz', 'teacher'])->first();
        if (!$cert) {
            return response()->json(['success' => false, 'message' => 'الشهادة غير صالحة أو غير مسجلة'], 404);
        }
        return response()->json(['success' => true, 'data' => $cert]);
    }

    public function leaderboard()
    {
        $leaders = GamificationProfile::with('user')->orderByDesc('xp')->take(20)->get();
        return response()->json(['success' => true, 'data' => $leaders]);
    }

    public function resources()
    {
        return response()->json(['success' => true, 'data' => Resource::where('status', 'PUBLISHED')->with('subject')->latest()->get()]);
    }

    public function missions()
    {
        return response()->json(['success' => true, 'data' => Mission::where('active', true)->get()]);
    }
}
