<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Battle;
use App\Models\BattleParticipant;
use App\Models\Question;
use App\Models\XpTransaction;
use App\Models\GamificationProfile;

class BattleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recentBattles = Battle::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['participants.user', 'winner'])->latest()->take(5)->get();

        return view('battle.index', compact('recentBattles'));
    }

    public function start()
    {
        $user = Auth::user();

        // Fetch 3 random questions or fallback questions
        $questions = Question::where('type', 'MCQ_SINGLE')->with('choices')->inRandomOrder()->take(3)->get();

        $battleData = [];
        if ($questions->count() >= 3) {
            foreach ($questions as $q) {
                $correctIndex = $q->choices->search(fn($c) => $c->is_correct);
                $battleData[] = [
                    'id' => $q->id,
                    'text' => $q->text,
                    'options' => $q->choices->pluck('text')->toArray(),
                    'correctIndex' => $correctIndex !== false ? $correctIndex : 0,
                    'points' => $q->points * 100,
                ];
            }
        } else {
            $battleData = [
                [
                    'id' => 1,
                    'text' => 'أي لغة برمجية تُستخدم بشكل رئيسي لإضافة التفاعلية والديناميكية إلى صفحات الويب؟',
                    'options' => ['JavaScript ⚡', 'HTML', 'CSS', 'SQL'],
                    'correctIndex' => 0,
                    'points' => 100,
                ],
                [
                    'id' => 2,
                    'text' => 'ما هو ناتج العملية الحسابية: 8 + (6 ÷ 2)؟',
                    'options' => ['7', '11 🎯', '14', '16'],
                    'correctIndex' => 1,
                    'points' => 100,
                ],
                [
                    'id' => 3,
                    'text' => 'ما هو البروتوكول المشفر القياسي المستخدم لتصفح مواقع الويب الآمنة؟',
                    'options' => ['HTTP', 'FTP', 'HTTPS 🔒', 'SSH'],
                    'correctIndex' => 2,
                    'points' => 100,
                ],
            ];
        }

        $battleCode = 'BTL-' . strtoupper(substr(uniqid(), -6));
        $battle = Battle::create([
            'code' => $battleCode,
            'mode' => 'STUDENT_VS_AI',
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ]);

        BattleParticipant::create([
            'battle_id' => $battle->id,
            'user_id' => $user->id,
            'is_ai' => false,
            'score' => 0,
            'joined_at' => now(),
        ]);

        BattleParticipant::create([
            'battle_id' => $battle->id,
            'user_id' => null,
            'is_ai' => true,
            'score' => 0,
            'joined_at' => now(),
        ]);

        return view('battle.arena', compact('battle', 'battleData', 'user'));
    }

    public function finish(Request $request, $id)
    {
        $user = Auth::user();
        $battle = Battle::findOrFail($id);

        $studentScore = (int) $request->input('student_score', 0);
        $aiScore = (int) $request->input('ai_score', 0);

        $studentParticipant = BattleParticipant::where('battle_id', $battle->id)
            ->where('user_id', $user->id)
            ->first();
        if ($studentParticipant) {
            $studentParticipant->update(['score' => $studentScore]);
        }

        $aiParticipant = BattleParticipant::where('battle_id', $battle->id)
            ->where('is_ai', true)
            ->first();
        if ($aiParticipant) {
            $aiParticipant->update(['score' => $aiScore]);
        }

        $winnerId = ($studentScore > $aiScore) ? $user->id : null;

        $battle->update([
            'status' => 'COMPLETED',
            'ended_at' => now(),
            'winner_id' => $winnerId,
        ]);

        if ($winnerId === $user->id) {
            // Reward 50 XP
            XpTransaction::create([
                'user_id' => $user->id,
                'amount' => 50,
                'reason' => 'الفوز في معركة Quiz Battle 1v1 🏆',
                'reference_type' => 'BATTLE_WIN',
                'reference_id' => (string) $battle->id,
                'idempotency_key' => "BATTLE_WIN_{$battle->id}_{$user->id}",
            ]);

            if ($user->gamificationProfile) {
                $user->gamificationProfile->increment('xp', 50);
            }
        }

        return response()->json([
            'success' => true,
            'winner_id' => $winnerId,
            'student_score' => $studentScore,
            'ai_score' => $aiScore,
            'xp_awarded' => ($winnerId === $user->id ? 50 : 10),
        ]);
    }
}
