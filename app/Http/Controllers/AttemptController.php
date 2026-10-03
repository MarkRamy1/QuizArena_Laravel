<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\Certificate;
use App\Models\XpTransaction;
use App\Models\GamificationProfile;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Mission;
use App\Models\UserMission;
use App\Models\Notification;
use App\Helpers\ArabicHelper;
use App\Helpers\CryptoHelper;
use App\Helpers\StreakHelper;

class AttemptController extends Controller
{
    public function start($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('status', 'PUBLISHED')->findOrFail($quizId);

        // 1. Check for existing in-progress attempt to resume
        $activeAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->first();

        if ($activeAttempt) {
            // Check if not expired
            if (now()->lessThan($activeAttempt->expires_at)) {
                return redirect()->route('attempts.take', $activeAttempt->id);
            }
            // Auto submit expired attempt
            return $this->autoSubmitExpired($activeAttempt);
        }

        // 2. Check attempt limits
        $completedAttemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->where('status', 'SUBMITTED')
            ->count();

        if ($quiz->attempt_limit > 0 && $completedAttemptsCount >= $quiz->attempt_limit) {
            $lastAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $user->id)
                ->latest()
                ->first();
            return redirect()->route('attempts.result', $lastAttempt->id)
                ->with('info', 'لقد استنفدت الحد المسموح به من المحاولات لهذا الاختبار.');
        }

        // 3. Create fresh attempt
        $startedAt = now();
        $expiresAt = $startedAt->copy()->addMinutes($quiz->duration_minutes);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'status' => 'IN_PROGRESS',
            'grading_status' => 'PENDING',
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'attempt_number' => $completedAttemptsCount + 1,
        ]);

        return redirect()->route('attempts.take', $attempt->id);
    }

    public function take($attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::with(['quiz.questions.choices', 'answers'])
            ->findOrFail($attemptId);

        if ($attempt->student_id !== $user->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه المحاولة');
        }

        if ($attempt->status === 'SUBMITTED') {
            return redirect()->route('attempts.result', $attempt->id);
        }

        // Check expiration
        if (now()->greaterThanOrEqualTo($attempt->expires_at)) {
            return $this->autoSubmitExpired($attempt);
        }

        // Calculate remaining seconds
        $remainingSeconds = max(0, $attempt->expires_at->diffInSeconds(now()));
        $answersMap = $attempt->answers->keyBy('question_id');

        return view('attempts.take', compact('attempt', 'remainingSeconds', 'answersMap'));
    }

    public function saveAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::findOrFail($attemptId);

        if ($attempt->student_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        if ($attempt->status !== 'IN_PROGRESS') {
            return response()->json(['success' => false, 'message' => 'المحاولة منتهية أو تم تسليمها'], 400);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'choice_id' => 'nullable|exists:question_choices,id',
            'text_answer' => 'nullable|string',
            'is_marked_for_review' => 'nullable|boolean',
        ]);

        $answer = AttemptAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'choice_id' => $validated['choice_id'] ?? null,
                'text_answer' => $validated['text_answer'] ?? null,
                'is_marked_for_review' => !empty($validated['is_marked_for_review']),
                'answered_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'answer' => $answer]);
    }

    public function submit(Request $request, $attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::with(['quiz.questions.choices', 'answers'])
            ->findOrFail($attemptId);

        if ($attempt->student_id !== $user->id) {
            abort(403, 'غير مصرح لك بتسليم هذه المحاولة');
        }

        if ($attempt->status === 'SUBMITTED') {
            return redirect()->route('attempts.result', $attempt->id);
        }

        // Save any last submitted answers from form if present
        if ($request->has('answers') && is_array($request->input('answers'))) {
            foreach ($request->input('answers') as $qId => $val) {
                $question = Question::find($qId);
                if ($question) {
                    if ($question->type === 'MCQ_SINGLE') {
                        AttemptAnswer::updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $qId],
                            ['choice_id' => $val, 'answered_at' => now()]
                        );
                    } else {
                        AttemptAnswer::updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $qId],
                            ['text_answer' => $val, 'answered_at' => now()]
                        );
                    }
                }
            }
        }

        return $this->gradeAndFinalizeAttempt($attempt);
    }

    private function autoSubmitExpired(QuizAttempt $attempt)
    {
        $attempt->load(['quiz.questions.choices', 'answers']);
        return $this->gradeAndFinalizeAttempt($attempt);
    }

    private function gradeAndFinalizeAttempt(QuizAttempt $attempt)
    {
        return DB::transaction(function () use ($attempt) {
            $attempt->load(['quiz.questions.choices', 'answers']);
            $user = $attempt->student;
            $quiz = $attempt->quiz;

            $totalPointsAwarded = 0;
            $maxPoints = 0;
            $answersMap = $attempt->answers->keyBy('question_id');

            foreach ($quiz->questions as $question) {
                $maxPoints += $question->points;
                $studentAns = $answersMap->get($question->id);

                $isCorrect = false;
                $pointsAwarded = 0;

                if ($studentAns) {
                    if ($question->type === 'MCQ_SINGLE') {
                        if ($studentAns->choice_id) {
                            $choice = $question->choices->firstWhere('id', $studentAns->choice_id);
                            if ($choice && $choice->is_correct) {
                                $isCorrect = true;
                                $pointsAwarded = $question->points;
                            }
                        }
                    } elseif ($question->type === 'SHORT_TEXT') {
                        if ($studentAns->text_answer && $question->model_answer) {
                            if (ArabicHelper::compareAnswers($studentAns->text_answer, $question->model_answer)) {
                                $isCorrect = true;
                                $pointsAwarded = $question->points;
                            }
                        }
                    }

                    $studentAns->update([
                        'is_correct' => $isCorrect,
                        'points_awarded' => $pointsAwarded,
                    ]);
                }

                $totalPointsAwarded += $pointsAwarded;
            }

            $percentage = $maxPoints > 0 ? (int) round(($totalPointsAwarded / $maxPoints) * 100) : 0;
            $submittedAt = now();
            $durationSeconds = max(1, $submittedAt->diffInSeconds($attempt->started_at));

            // Update attempt record
            $attempt->update([
                'status' => 'SUBMITTED',
                'grading_status' => 'AUTO_GRADED',
                'score' => $totalPointsAwarded,
                'max_score' => $maxPoints,
                'percentage' => $percentage,
                'duration_seconds' => $durationSeconds,
                'submitted_at' => $submittedAt,
            ]);

            // Calculate XP Award
            $xpAwarded = 25; // Base completion XP
            if ($percentage >= 90) {
                $xpAwarded += 50;
            } elseif ($percentage >= 70) {
                $xpAwarded += 25;
            }

            // XP Transaction (Idempotent)
            $idempotencyKey = "QUIZ_COMPLETE_{$attempt->id}";
            XpTransaction::firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'user_id' => $user->id,
                    'amount' => $xpAwarded,
                    'reason' => "إكمال اختبار: {$quiz->title} بنتيجة {$percentage}%",
                    'reference_type' => 'QUIZ_COMPLETION',
                    'reference_id' => (string) $attempt->id,
                ]
            );

            // Streak & Gamification Profile Update
            $todayStr = now()->format('Y-m-d');
            $gamification = $user->gamificationProfile ?? GamificationProfile::create([
                'user_id' => $user->id,
                'xp' => 0,
                'level' => 1,
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null,
            ]);

            $streakResult = StreakHelper::evaluateStreak(
                $gamification->last_activity_date,
                $todayStr,
                $gamification->current_streak
            );

            $newStreak = $streakResult['streak'];
            $newTotalXp = $gamification->xp + $xpAwarded;
            $newLevel = (int) floor($newTotalXp / 100) + 1;

            $gamification->update([
                'xp' => $newTotalXp,
                'level' => $newLevel,
                'current_streak' => $newStreak,
                'longest_streak' => max($newStreak, $gamification->longest_streak),
                'last_activity_date' => $todayStr,
            ]);

            // Certificate Evaluation
            $shouldIssueCert = false;
            if ($quiz->certificate_policy === 'COMPLETION') {
                $shouldIssueCert = true;
            } elseif ($quiz->certificate_policy === 'PASSING_SCORE') {
                $shouldIssueCert = ($percentage >= $quiz->certificate_min_score);
            }

            $certificate = null;
            if ($shouldIssueCert) {
                $certNumber = 'QA-' . strtoupper(dechex(time())) . '-' . strtoupper(substr(uniqid(), -4));
                $verificationCode = CryptoHelper::generateCertificateCode();

                $certificate = Certificate::firstOrCreate(
                    ['attempt_id' => $attempt->id],
                    [
                        'student_id' => $user->id,
                        'quiz_id' => $quiz->id,
                        'teacher_id' => $quiz->teacher_id,
                        'certificate_number' => $certNumber,
                        'verification_code' => $verificationCode,
                        'percentage' => $percentage,
                        'issued_at' => now(),
                    ]
                );

                // Notification for certificate
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'CERTIFICATE_ISSUED',
                    'title' => '🎉 تم إصدار شهادة إنجاز جديدة!',
                    'message' => "تهانينا! لقد حصلت على شهادة اجتياز اختبار ({$quiz->title}) بنسبة {$percentage}%.",
                    'data' => ['certificate_id' => $certificate->id, 'code' => $certificate->verification_code],
                ]);
            }

            // Achievements Evaluation
            $completedAttemptsCount = QuizAttempt::where('student_id', $user->id)
                ->where('status', 'SUBMITTED')
                ->count();

            // 1. FIRST_QUIZ
            if ($completedAttemptsCount >= 1) {
                $this->unlockAchievement($user, 'FIRST_QUIZ');
            }
            // 2. SCORE_90_PLUS
            if ($percentage >= 90) {
                $this->unlockAchievement($user, 'SCORE_90_PLUS');
            }
            // 3. THREE_QUIZZES
            if ($completedAttemptsCount >= 3) {
                $this->unlockAchievement($user, 'THREE_QUIZZES');
            }
            // 4. FIVE_CERTIFICATES
            $certCount = Certificate::where('student_id', $user->id)->count();
            if ($certCount >= 5) {
                $this->unlockAchievement($user, 'FIVE_CERTIFICATES');
            }

            // Daily Mission: COMPLETE_QUIZ
            $mission = Mission::where('type', 'COMPLETE_QUIZ')->where('active', true)->first();
            if ($mission) {
                UserMission::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'mission_id' => $mission->id,
                        'date' => $todayStr,
                    ],
                    [
                        'progress' => 1,
                        'completed' => true,
                        'completed_at' => now(),
                    ]
                );
            }

            return redirect()->route('attempts.result', $attempt->id)
                ->with('success', "تم تسليم الاختبار وتصحيحه بنجاح! نلت {$xpAwarded} نقطة خبرة (XP) 🔥");
        });
    }

    private function unlockAchievement($user, $key)
    {
        $ach = Achievement::where('key', $key)->first();
        if ($ach) {
            $unlocked = UserAchievement::firstOrCreate(
                ['user_id' => $user->id, 'achievement_id' => $ach->id],
                ['unlocked_at' => now()]
            );

            if ($unlocked->wasRecentlyCreated && $ach->xp_reward > 0) {
                XpTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $ach->xp_reward,
                    'reason' => "فتح إنجاز: {$ach->title}",
                    'reference_type' => 'ACHIEVEMENT_UNLOCK',
                    'reference_id' => (string) $ach->id,
                    'idempotency_key' => "ACH_UNLOCK_{$user->id}_{$ach->id}",
                ]);

                if ($user->gamificationProfile) {
                    $user->gamificationProfile->increment('xp', $ach->xp_reward);
                }
            }
        }
    }

    public function result($attemptId)
    {
        $user = Auth::user();
        $attempt = QuizAttempt::with([
            'quiz.questions.choices',
            'answers.choice',
            'certificate',
        ])->findOrFail($attemptId);

        if ($attempt->student_id !== $user->id && !$user->isTeacher()) {
            abort(403, 'غير مصرح لك بعرض هذه النتيجة');
        }

        $answersMap = $attempt->answers->keyBy('question_id');
        $passed = $attempt->percentage >= $attempt->quiz->passing_score;

        return view('attempts.result', compact('attempt', 'answersMap', 'passed'));
    }
}
