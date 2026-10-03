<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\LiveQuizSession;
use App\Models\AuditLog;

class LiveQuizController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeSessions = LiveQuizSession::where('status', '!=', 'FINISHED')
            ->with(['quiz', 'teacher'])
            ->latest()
            ->take(5)
            ->get();

        $myQuizzes = ($user->isTeacher() || $user->isAdmin())
            ? Quiz::where('teacher_id', $user->id)->orWhere('status', 'PUBLISHED')->get()
            : collect();

        return view('live.index', compact('activeSessions', 'myQuizzes'));
    }

    public function start(Request $request, Quiz $quiz)
    {
        abort_unless(Auth::user()->isTeacher() || Auth::user()->isAdmin(), 403);

        $pin = (string) rand(100000, 999999);

        $session = LiveQuizSession::create([
            'quiz_id' => $quiz->id,
            'teacher_id' => Auth::id(),
            'pin_code' => $pin,
            'status' => 'WAITING',
            'current_question_index' => 0,
            'participants' => [
                ['id' => Auth::id(), 'name' => Auth::user()->name . ' (المضيف)', 'avatar' => Auth::user()->avatar_url, 'score' => 0]
            ],
        ]);

        AuditLog::log('LIVE_QUIZ_STARTED', "بدأ المعلم " . Auth::user()->name . " جلسة اختبار حية برمز PIN: {$pin}", Auth::id());

        return redirect()->route('live-quiz.host', $session->id);
    }

    public function host(LiveQuizSession $session)
    {
        abort_unless(Auth::user()->id === $session->teacher_id || Auth::user()->isAdmin(), 403);
        $session->load(['quiz.questions.options', 'teacher']);
        return view('live.host', compact('session'));
    }

    public function join(Request $request)
    {
        $pin = trim($request->input('pin') ?? $request->input('pin_code') ?? '');
        $session = LiveQuizSession::where('pin_code', $pin)->where('status', '!=', 'FINISHED')->first();

        if (!$session) {
            return back()->with('error', 'رمز PIN غير صحيح أو الجلسة الحية قد انتهت.');
        }

        // Add user to participants if not present
        $user = Auth::user();
        $participants = $session->participants ?? [];
        $exists = collect($participants)->contains('id', $user->id);

        if (!$exists) {
            $participants[] = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'score' => 0,
            ];
            $session->update(['participants' => $participants]);
        }

        return redirect()->route('live-quiz.room', $session->id);
    }

    public function room(LiveQuizSession $session)
    {
        $session->load(['quiz.questions.options', 'teacher']);
        return view('live.room', compact('session'));
    }

    public function submitLiveAnswer(Request $request, LiveQuizSession $session)
    {
        $user = Auth::user();
        $isCorrect = (bool) $request->input('is_correct', false);
        $timeRemaining = (int) $request->input('time_remaining', 10);

        // Speed points formula: 100 base points + timeRemaining * 10
        $points = $isCorrect ? (100 + ($timeRemaining * 10)) : 0;

        $participants = $session->participants ?? [];
        foreach ($participants as &$p) {
            if ($p['id'] === $user->id) {
                $p['score'] = ($p['score'] ?? 0) + $points;
                break;
            }
        }
        $session->update(['participants' => $participants]);

        if ($points > 0) {
            $user->addXp((int) round($points / 10));
        }

        return response()->json([
            'success' => true,
            'points_awarded' => $points,
            'is_correct' => $isCorrect,
        ]);
    }
}
