@extends('layouts.app')

@section('title', $quiz->title . ' — غرفة الاختبار والمراقبة الذكية')

@section('content')
<div style="max-width: 950px; margin: 0 auto; position: relative;">

    <!-- Sticky Exam Topbar with Overall Timer and Question Stepper -->
    <div style="position: sticky; top: 76px; z-index: 40; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 24px; box-shadow: var(--shadow-md); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h2 style="font-size: 17px; font-weight: 800; margin: 0;">{{ $quiz->title }}</h2>
                @if($quiz->is_ai_proctored)
                <span class="role-pill" style="font-size: 11px; background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3);">
                    🛡️ مراقبة ذكية AI نشطة
                </span>
                @endif
            </div>
            <span style="font-size: 12.5px; color: var(--text-muted);">
                السؤال <span id="currentQuestionNumDisplay">1</span> من {{ $quiz->questions->count() }} &bull; إجمالي الدرجات: {{ $quiz->questions->sum('points') }}
            </span>
        </div>

        <!-- Timers Display -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <!-- Question-Specific Countdown Timer -->
            <div id="qTimerContainer" style="display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 800; background: rgba(79, 70, 229, 0.15); padding: 6px 14px; border-radius: 999px; border: 1px solid rgba(79, 70, 229, 0.4); color: var(--primary-light);">
                <span>⏳ وقت السؤال:</span>
                <span id="qTimerDisplay" style="font-family: monospace; font-size: 17px;">--s</span>
            </div>

            <!-- Overall Quiz Countdown Timer -->
            <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 800; background: var(--bg); padding: 6px 14px; border-radius: 999px; border: 1px solid var(--surface-border);">
                <span>⏱️ الوقت الكلي:</span>
                <span id="overallTimerDisplay" style="color: var(--accent); font-family: monospace; font-size: 17px;">--:--</span>
            </div>

            <button type="button" class="btn btn-sm btn-primary" onclick="confirmSubmit()">
                ✅ إنهاء وتسليم
            </button>
        </div>
    </div>

    <!-- Attached PDF Exam Document if provided by Teacher -->
    @if($quiz->pdf_exam_path)
    <div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(79, 70, 229, 0.05)); border: 1px solid rgba(6, 182, 212, 0.3); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 28px;">📄</span>
            <div>
                <div style="font-weight: 800; font-size: 14.5px;">ورقة الاختبار المرفقة (نسخة PDF رسمية من المعلم)</div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $quiz->pdf_exam_name ?? 'ورقة_الامتحان.pdf' }}</div>
            </div>
        </div>
        <a href="{{ route('quizzes.downloadPdf', $quiz) }}" target="_blank" class="btn btn-sm btn-accent">
            ⬇️ تحميل وقراءة ورقة الاختبار PDF
        </a>
    </div>
    @endif

    <!-- AI Proctor Floating Live Monitor Widget -->
    @if($quiz->is_ai_proctored)
    <div id="proctorHud" style="position: fixed; bottom: 20px; left: 20px; z-index: 999; background: var(--bg-elevated); border: 2px solid var(--surface-border); border-radius: var(--radius-lg); padding: 12px; box-shadow: var(--shadow-lg); width: 220px; text-align: center; transition: all 0.3s;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12px; font-weight: 800; color: var(--success); display: flex; align-items: center; gap: 5px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--success); display: inline-block; animation: pulse 1.5s infinite;"></span>
                المراقب الذكي AI
            </span>
            <span id="violationsBadge" style="font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3);">
                المخالفات: <span id="violationCountDisplay">0</span>/3
            </span>
        </div>

        <!-- Camera Preview Container -->
        <div style="position: relative; width: 100%; height: 120px; background: #000; border-radius: var(--radius-sm); overflow: hidden; display: flex; align-items: center; justify-content: center;">
            <video id="proctorVideo" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); display: none;"></video>
            <div id="cameraFallback" style="font-size: 11.5px; color: var(--text-muted); padding: 8px;">
                <div style="font-size: 24px; margin-bottom: 4px;">📡</div>
                <span>المستشعر الذكي نشط<br><b style="color: var(--accent);">مراقبة حركات الشاشة</b></span>
            </div>
            <!-- Face scanning grid overlay -->
            <div style="position: absolute; inset: 0; border: 1px dashed rgba(6, 182, 212, 0.4); pointer-events: none; border-radius: var(--radius-sm);"></div>
        </div>

        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 6px;">
            ممنوع مغادرة الشاشة أو تبديل النوافذ
        </div>
    </div>
    @endif

    <!-- Exam Questions Form -->
    <form id="examForm" action="{{ route('quizzes.submitAttempt', [$quiz, $attempt]) }}" method="POST">
        @csrf
        <input type="hidden" name="proctor_violations_count" id="proctorViolationsInput" value="0">
        <input type="hidden" name="proctor_status" id="proctorStatusInput" value="PASSED_CLEAN">
        <input type="hidden" name="proctor_log" id="proctorLogInput" value="">

        <!-- Stepper Navigation Bar -->
        <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px;">
            @foreach($quiz->questions as $i => $quest)
            <button type="button" class="btn btn-sm q-step-btn" id="stepBtn_{{ $i }}" onclick="showQuestion({{ $i }})" style="min-width: 40px; padding: 6px 12px; font-weight: 800; {{ $i === 0 ? 'border-color: var(--primary); background: var(--primary); color: #fff;' : 'background: var(--surface); color: var(--text-muted);' }}">
                {{ $i + 1 }}
            </button>
            @endforeach
        </div>

        <!-- Question Cards -->
        <div id="questionsWrapper">
            @foreach($quiz->questions as $index => $q)
            @php
                $qTimeLimit = $q->time_limit_seconds > 0 ? $q->time_limit_seconds : ($quiz->has_per_question_timer ? $quiz->per_question_seconds : 0);
            @endphp
            <div class="card question-card" id="qCard_{{ $index }}" data-index="{{ $index }}" data-time-limit="{{ $qTimeLimit }}" style="border-top: 4px solid var(--primary); display: {{ $index === 0 ? 'block' : 'none' }};">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 13.5px; font-weight: 800; color: var(--primary);">
                            {{ __('app.question') }} {{ $index + 1 }} &bull;
                            @if($q->type === 'CHOOSE') {{ __('app.q_choose') }}
                            @elseif($q->type === 'TRUE_FALSE') {{ __('app.q_true_false') }}
                            @elseif($q->type === 'COMPLETE') {{ __('app.q_complete') }}
                            @elseif($q->type === 'MATCH') {{ __('app.q_match') }}
                            @elseif($q->type === 'ESSAY') {{ __('app.q_essay') }}
                            @endif
                        </span>
                        @if($qTimeLimit > 0)
                        <span class="role-pill" style="font-size: 11px; background: rgba(79, 70, 229, 0.15); color: var(--primary-light);">
                            ⏳ وقت هذا السؤال: {{ $qTimeLimit }} ثانية
                        </span>
                        @endif
                    </div>
                    <span class="role-pill">{{ $q->points }} {{ __('app.points') }}</span>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; line-height: 1.6;">
                    {{ $q->question_text }}
                </h3>

                <!-- 1. CHOOSE & TRUE_FALSE -->
                @if($q->type === 'CHOOSE' || $q->type === 'TRUE_FALSE')
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($q->options as $opt)
                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: var(--radius-md); background: var(--surface); border: 1px solid var(--surface-border); cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}" style="width: auto; cursor: pointer;">
                        <span style="font-size: 15.5px; font-weight: 600;">{{ $opt->option_text }}</span>
                    </label>
                    @endforeach
                </div>

                <!-- 2. COMPLETE (Fill in blank) -->
                @elseif($q->type === 'COMPLETE')
                <div class="form-group">
                    <label class="form-label">اكتب إجابتك النموذجية لإكمال الفراغ:</label>
                    <input type="text" name="answers[{{ $q->id }}]" class="form-control" placeholder="اكتب الكلمة أو العبارة المطلوبة هنا..." autocomplete="off">
                </div>

                <!-- 3. MATCH (Matching pairs) -->
                @elseif($q->type === 'MATCH')
                @php
                    $pairs = $q->match_pairs ?? [];
                    $rightItems = collect($pairs)->pluck('right')->shuffle();
                @endphp
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">اختر من القائمة المنسدلة العنصر المطابق لكل بند:</p>
                    @foreach($pairs as $pIdx => $pair)
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; align-items: center; background: var(--surface); padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border);">
                        <div style="font-weight: 700; font-size: 14.5px;">
                            {{ $pair['left'] }}
                        </div>
                        <div>
                            <select name="answers[{{ $q->id }}][{{ $pIdx }}]" class="form-control">
                                <option value="">-- اختر المطابق --</option>
                                @foreach($rightItems as $rVal)
                                <option value="{{ $rVal }}">{{ $rVal }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- 4. ESSAY -->
                @elseif($q->type === 'ESSAY')
                <div class="form-group">
                    <label class="form-label">اكتب إجابتك بالتفصيل والشرح:</label>
                    <textarea name="answers[{{ $q->id }}]" class="form-control" rows="5" placeholder="اكتب تحليلك وإجابتك المقالية هنا..."></textarea>
                </div>
                @endif

                <!-- Question Card Footer Navigation -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--surface-border);">
                    <button type="button" class="btn btn-sm" onclick="prevQuestion()" {{ $index === 0 ? 'disabled' : '' }}>
                        &rarr; السؤال السابق
                    </button>

                    @if($index < $quiz->questions->count() - 1)
                    <button type="button" class="btn btn-primary btn-sm" onclick="nextQuestion()">
                        السؤال التالي &larr;
                    </button>
                    @else
                    <button type="button" class="btn btn-primary" onclick="confirmSubmit()">
                        🚀 إنهاء وتسليم الاختبار
                    </button>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

    </form>

</div>

<!-- Proctoring Warning Modal -->
<div id="proctorAlertModal" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-elevated); border: 2px solid var(--danger); border-radius: var(--radius-xl); padding: 32px; max-width: 520px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(239,68,68,0.4);">
        <div style="font-size: 54px; margin-bottom: 12px; animation: bounce 0.6s infinite alternate;">⚠️</div>
        <h2 style="font-size: 22px; font-weight: 900; color: var(--danger); margin-bottom: 10px;">
            تحذير من المراقب الذكي (AI Proctor Alert)!
        </h2>
        <p id="proctorAlertMsg" style="font-size: 15px; line-height: 1.6; margin-bottom: 18px; color: var(--text-main);">
            تم رصد محاولة لمغادرة شاشة الاختبار أو تبديل النافذة!
        </p>
        <div style="background: var(--surface); padding: 12px; border-radius: var(--radius-md); font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
            عدد المخالفات المسجلة حتى الآن: <b id="modalViolationCount" style="color: var(--danger); font-size: 16px;">1</b> من 3.<br>
            عند بلوغ 3 مخالفات، سيتم تسليم الاختبار تلقائياً وإلغاؤه لشبهة الغش.
        </div>
        <button type="button" class="btn btn-primary" onclick="closeProctorAlert()" style="width: 100%; padding: 12px; font-size: 16px;">
            أوافق وأتعهد بالالتزام بالنزاهة والعودة للامتحان
        </button>
    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
@keyframes bounce {
    from { transform: translateY(0); }
    to { transform: translateY(-8px); }
}
.flash-border {
    outline: 6px solid var(--danger) !important;
}
</style>

<script>
    // -------------------------------------------------------------
    // 1. QUESTION STEPPER & SPECIFIC PER-QUESTION TIMER
    // -------------------------------------------------------------
    let currentQIdx = 0;
    const totalQuestions = {{ $quiz->questions->count() }};
    const questionCards = document.querySelectorAll('.question-card');
    let qTimerInterval = null;
    let qTimeRemaining = 0;

    function showQuestion(idx) {
        if (idx < 0 || idx >= totalQuestions) return;
        currentQIdx = idx;

        // Hide all cards and show active card
        questionCards.forEach((c, i) => {
            c.style.display = (i === idx) ? 'block' : 'none';
        });

        // Update step buttons
        document.querySelectorAll('.q-step-btn').forEach((btn, i) => {
            if (i === idx) {
                btn.style.borderColor = 'var(--primary)';
                btn.style.background = 'var(--primary)';
                btn.style.color = '#fff';
            } else {
                btn.style.borderColor = 'var(--surface-border)';
                btn.style.background = 'var(--surface)';
                btn.style.color = 'var(--text-muted)';
            }
        });

        document.getElementById('currentQuestionNumDisplay').innerText = (idx + 1);

        // Setup Per-Question Timer
        startQuestionTimer(idx);
    }

    function startQuestionTimer(idx) {
        clearInterval(qTimerInterval);
        const card = document.getElementById('qCard_' + idx);
        const timeLimit = parseInt(card.getAttribute('data-time-limit')) || 0;
        const qTimerContainer = document.getElementById('qTimerContainer');
        const qTimerDisplay = document.getElementById('qTimerDisplay');

        if (timeLimit > 0) {
            qTimerContainer.style.display = 'flex';
            qTimeRemaining = timeLimit;
            qTimerDisplay.innerText = qTimeRemaining + 's';
            qTimerDisplay.style.color = 'var(--primary-light)';

            qTimerInterval = setInterval(() => {
                qTimeRemaining--;
                qTimerDisplay.innerText = qTimeRemaining + 's';

                if (qTimeRemaining <= 10) {
                    qTimerDisplay.style.color = 'var(--danger)';
                }

                if (qTimeRemaining <= 0) {
                    clearInterval(qTimerInterval);
                    alert("انتهى الوقت المخصص لهذا السؤال (" + timeLimit + " ثانية). سيتم الانتقال للسؤال التالي تلقائياً.");
                    if (currentQIdx < totalQuestions - 1) {
                        nextQuestion();
                    } else {
                        confirmSubmit();
                    }
                }
            }, 1000);
        } else {
            qTimerContainer.style.display = 'none';
        }
    }

    function nextQuestion() {
        if (currentQIdx < totalQuestions - 1) {
            showQuestion(currentQIdx + 1);
        }
    }

    function prevQuestion() {
        if (currentQIdx > 0) {
            showQuestion(currentQIdx - 1);
        }
    }

    // Initialize first question
    showQuestion(0);

    // -------------------------------------------------------------
    // 2. OVERALL COUNTDOWN TIMER
    // -------------------------------------------------------------
    const durationMinutes = {{ $quiz->duration_minutes }};
    const startTime = new Date("{{ $attempt->started_at->toIso8601String() }}").getTime();
    const endTime = startTime + (durationMinutes * 60 * 1000);

    function updateOverallTimer() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance <= 0) {
            document.getElementById('overallTimerDisplay').innerText = "00:00";
            alert("انتهى وقت الاختبار الكلي المحدد! سيتم تسليم إجاباتك الآن تلقائياً.");
            document.getElementById('examForm').submit();
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('overallTimerDisplay').innerText = 
            (minutes < 10 ? "0" : "") + minutes + ":" + 
            (seconds < 10 ? "0" : "") + seconds;
    }

    setInterval(updateOverallTimer, 1000);
    updateOverallTimer();

    // -------------------------------------------------------------
    // 3. AI PROCTORING & ANTI-CHEATING SURVEILLANCE
    // -------------------------------------------------------------
    const isAiProctored = {{ $quiz->is_ai_proctored ? 'true' : 'false' }};
    let violationCount = 0;
    const maxViolations = 3;
    const proctorLog = [];

    function recordViolation(type, description) {
        if (!isAiProctored) return;

        violationCount++;
        const logEntry = {
            time: new Date().toLocaleTimeString(),
            type: type,
            description: description,
            strike: violationCount
        };
        proctorLog.push(logEntry);

        // Update hidden inputs
        document.getElementById('proctorViolationsInput').value = violationCount;
        document.getElementById('proctorLogInput').value = JSON.stringify(proctorLog);
        document.getElementById('violationCountDisplay').innerText = violationCount;

        // Visual Audio alert
        document.body.classList.add('flash-border');
        setTimeout(() => document.body.classList.remove('flash-border'), 1000);
        playAlertTone();

        // Show Modal Alert
        document.getElementById('modalViolationCount').innerText = violationCount;
        document.getElementById('proctorAlertMsg').innerText = description;
        document.getElementById('proctorAlertModal').style.display = 'flex';

        if (violationCount >= maxViolations) {
            document.getElementById('proctorStatusInput').value = 'FLAGGED_SUSPICIOUS';
            alert("🚨 تم تجاوز الحد الأقصى من المخالفات المسموحة (3 مخالفات). سيتم إلغاء الاختبار وتسليم الإجابات فوراً للمراجعة كحالة اشتباه غش.");
            document.getElementById('examForm').submit();
        }
    }

    function closeProctorAlert() {
        document.getElementById('proctorAlertModal').style.display = 'none';
        // Request fullscreen again if exited
        if (!document.fullscreenElement) {
            requestFullScreenMode();
        }
    }

    function playAlertTone() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(440, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.3);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.4);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch (e) {}
    }

    function requestFullScreenMode() {
        const el = document.documentElement;
        if (el.requestFullscreen) {
            el.requestFullscreen().catch(() => {});
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        }
    }

    if (isAiProctored) {
        // Request Camera for proctoring HUD
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(stream => {
                    const video = document.getElementById('proctorVideo');
                    if (video) {
                        video.srcObject = stream;
                        video.style.display = 'block';
                        document.getElementById('cameraFallback').style.display = 'none';
                    }
                })
                .catch(err => {
                    // Fallback to sensor radar view
                });
        }

        // Fullscreen Enforcement on first click
        window.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                requestFullScreenMode();
            }
        }, { once: true });

        // 1. Detect Tab Switch / Page Visibility Change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                recordViolation('TAB_SWITCH', 'تم رصد تبديل تبويب المتصفح أو مغادرة صفحة الاختبار.');
            }
        });

        // 2. Detect Window Blur (clicking outside or alt-tab)
        window.addEventListener('blur', () => {
            recordViolation('WINDOW_BLUR', 'تم رصد النقر خارج نافذة الامتحان أو محاولة تشغيل برنامج آخر.');
        });

        // 3. Detect Fullscreen Exit
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                recordViolation('FULLSCREEN_EXIT', 'تم الخروج من وضع ملء الشاشة أثناء الاختبار.');
            }
        });

        // 4. Prevent Context Menu (Right Click)
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            recordViolation('RIGHT_CLICK', 'محاولة النقر بزر الفأرة الأيمن محظورة أثناء الاختبار.');
        });

        // 5. Prevent Copy, Cut, Paste
        document.addEventListener('copy', (e) => {
            e.preventDefault();
            recordViolation('COPY_ATTEMPT', 'محاولة نسخ نصوص الاختبار محظورة.');
        });
        document.addEventListener('paste', (e) => {
            e.preventDefault();
            recordViolation('PASTE_ATTEMPT', 'محاولة لصق نصوص محظورة.');
        });

        // 6. Block DevTools Shortcuts (F12, Ctrl+U, Ctrl+Shift+I)
        window.addEventListener('keydown', (e) => {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) || (e.ctrlKey && e.key === 'u')) {
                e.preventDefault();
                recordViolation('DEVTOOLS_KEY', 'محاولة استخدام اختصارات المطورين محظورة.');
            }
        });
    }

    function confirmSubmit() {
        if (confirm("هل أنت متأكد من رغبتك في تسليم الاختبار الآن؟ لن تتمكن من تعديل الإجابات بعد ذلك.")) {
            clearInterval(qTimerInterval);
            document.getElementById('examForm').submit();
        }
    }
</script>
@endsection
