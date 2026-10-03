@extends('layouts.app')

@section('title', 'قاعة الاختبار — ' . $attempt->quiz->title)

@section('content')
<div style="max-width: 950px; margin: auto;">
    <!-- Sticky Exam Header -->
    <div class="card" style="padding: 16px 22px; margin-bottom: 20px; position: sticky; top: 88px; z-index: 50; backdrop-filter: blur(15px); background: rgba(16, 24, 43, 0.95);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="pill-stat" style="font-size: 11px; margin-bottom: 2px;">
                    📝 اختبار رسمي قيد الحل
                </span>
                <h2 style="font-size: 18px; font-weight: 800;">{{ $attempt->quiz->title }}</h2>
            </div>

            <!-- Countdown Timer -->
            <div style="display: flex; align-items: center; gap: 14px;">
                <div id="timerDisplay" style="font-size: 22px; font-weight: 900; font-family: monospace; color: #f5c451; background: rgba(245, 196, 81, 0.1); border: 1px solid rgba(245, 196, 81, 0.3); padding: 6px 16px; border-radius: 12px;">
                    --:--
                </div>
                <button type="button" class="btn success small" onclick="confirmSubmit()">
                    تسليم الاختبار 🏁
                </button>
            </div>
        </div>

        <!-- Question Navigator Pills -->
        <div style="display: flex; gap: 8px; margin-top: 14px; overflow-x: auto; padding-bottom: 4px;">
            @foreach($attempt->quiz->questions as $idx => $q)
            @php
                $ans = $answersMap->get($q->id);
                $isAnswered = $ans && ($ans->choice_id || $ans->text_answer);
                $isFlagged = $ans && $ans->is_marked_for_review;
            @endphp
            <button type="button" class="nav-q-pill btn small" id="pill_{{ $idx }}" onclick="goToQuestion({{ $idx }})"
                style="min-width: 38px; text-align: center; font-weight: 800; font-size: 13px;
                @if($idx === 0) border-color: var(--secondary); background: rgba(0, 217, 255, 0.15); @endif
                @if($isAnswered) border-color: var(--success); color: #4ade80; @endif
                @if($isFlagged) border-color: var(--warning); color: #fbbf24; @endif">
                {{ $idx + 1 }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Questions Form -->
    <form action="{{ route('attempts.submit', $attempt->id) }}" method="POST" id="examForm">
        @csrf

        @foreach($attempt->quiz->questions as $idx => $question)
        @php
            $ans = $answersMap->get($question->id);
        @endphp
        <div class="card question-card" id="qCard_{{ $idx }}" style="{{ $idx > 0 ? 'display: none;' : '' }} padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span class="pill-stat" style="font-size: 13px; color: var(--secondary);">
                    السؤال {{ $idx + 1 }} من {{ $attempt->quiz->questions->count() }}
                </span>
                <span class="pill-stat xp" style="font-size: 12px;">
                    {{ $question->points }} {{ $question->points > 1 ? 'درجات' : 'درجة' }}
                </span>
            </div>

            <!-- Question Body -->
            <h3 style="font-size: 20px; font-weight: 800; line-height: 1.7; margin-bottom: 24px;">
                {{ $question->text }}
            </h3>

            <!-- MCQ Choices -->
            @if($question->type === 'MCQ_SINGLE')
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                @foreach($question->choices as $choice)
                <label class="card-inner option-label" style="display: flex; align-items: center; gap: 14px; padding: 16px; cursor: pointer; border-radius: var(--radius-md); transition: all 0.2s ease;">
                    <input type="radio"
                           name="answers[{{ $question->id }}]"
                           value="{{ $choice->id }}"
                           {{ ($ans && $ans->choice_id == $choice->id) ? 'checked' : '' }}
                           onchange="saveAnswerLive({{ $question->id }}, {{ $choice->id }}, null, {{ $idx }})"
                           style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--secondary);">
                    <span style="font-size: 16px; font-weight: 700;">{{ $choice->text }}</span>
                </label>
                @endforeach
            </div>

            <!-- Short Text Answer -->
            @elseif($question->type === 'SHORT_TEXT')
            <div style="margin-bottom: 24px;">
                <label for="textAns_{{ $question->id }}" style="font-size: 14px; margin-bottom: 8px;">
                    اكتب إجابتك هنا (سيتم فحص الإجابة آلياً بمعالجة النصوص):
                </label>
                <input type="text"
                       id="textAns_{{ $question->id }}"
                       name="answers[{{ $question->id }}]"
                       value="{{ $ans?->text_answer }}"
                       placeholder="اكتب إجابتك النموذجية..."
                       onblur="saveAnswerLive({{ $question->id }}, null, this.value, {{ $idx }})"
                       style="font-size: 16px; padding: 14px;">
            </div>
            @endif

            <!-- Flag For Review & Navigation Controls -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--line); padding-top: 20px; flex-wrap: wrap; gap: 12px;">
                <label style="display: flex; align-items: center; gap: 8px; margin: 0; cursor: pointer; font-size: 14px; font-weight: 700; color: #fbbf24;">
                    <input type="checkbox"
                           id="flag_{{ $idx }}"
                           {{ ($ans && $ans->is_marked_for_review) ? 'checked' : '' }}
                           onchange="toggleFlag({{ $question->id }}, this.checked, {{ $idx }})"
                           style="width: auto; accent-color: #fbbf24;">
                    🚩 تمييز للمراجعة لاحقاً
                </label>

                <div style="display: flex; gap: 10px;">
                    @if($idx > 0)
                    <button type="button" class="btn small" onclick="goToQuestion({{ $idx - 1 }})">
                        &rarr; السابق
                    </button>
                    @endif

                    @if($idx < $attempt->quiz->questions->count() - 1)
                    <button type="button" class="btn primary small" onclick="goToQuestion({{ $idx + 1 }})">
                        التالي &larr;
                    </button>
                    @else
                    <button type="button" class="btn success small" onclick="confirmSubmit()">
                        تسليم الاختبار النهائي 🏁
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </form>
</div>

<script>
    const totalQuestions = {{ $attempt->quiz->questions->count() }};
    let currentIdx = 0;
    let remainingSeconds = {{ $remainingSeconds }};
    const attemptId = {{ $attempt->id }};
    const csrfToken = '{{ csrf_token() }}';

    // Timer logic
    function updateTimer() {
        if (remainingSeconds <= 0) {
            document.getElementById('timerDisplay').innerText = '00:00';
            alert('انتهى الوقت المحدد للاختبار! سيتم تسليم إجاباتك تلقائياً الآن.');
            document.getElementById('examForm').submit();
            return;
        }

        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        const display = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

        const timerBox = document.getElementById('timerDisplay');
        timerBox.innerText = display;

        if (remainingSeconds < 120) {
            timerBox.style.color = '#ef4444';
            timerBox.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            timerBox.style.background = 'rgba(239, 68, 68, 0.15)';
        }

        remainingSeconds--;
    }

    setInterval(updateTimer, 1000);
    updateTimer();

    // Question navigation
    function goToQuestion(idx) {
        document.querySelectorAll('.question-card').forEach((c, i) => {
            c.style.display = (i === idx) ? 'block' : 'none';
        });

        document.querySelectorAll('.nav-q-pill').forEach((p, i) => {
            if (i === idx) {
                p.style.boxShadow = '0 0 0 2px var(--secondary)';
            } else {
                p.style.boxShadow = 'none';
            }
        });

        currentIdx = idx;
    }

    // Live AJAX Answer Saving
    function saveAnswerLive(questionId, choiceId, textAnswer, qIndex) {
        // Mark pill green as answered
        const pill = document.getElementById('pill_' + qIndex);
        if (pill) {
            pill.style.borderColor = 'var(--success)';
            pill.style.color = '#4ade80';
        }

        fetch(`{{ url('/attempts') }}/${attemptId}/answers`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                question_id: questionId,
                choice_id: choiceId,
                text_answer: textAnswer,
                is_marked_for_review: document.getElementById('flag_' + qIndex)?.checked || false,
            })
        }).catch(err => console.error('Save error:', err));
    }

    // Toggle Flag For Review
    function toggleFlag(questionId, isFlagged, qIndex) {
        const pill = document.getElementById('pill_' + qIndex);
        if (pill) {
            if (isFlagged) {
                pill.style.borderColor = 'var(--warning)';
                pill.style.color = '#fbbf24';
            } else {
                pill.style.borderColor = 'var(--line)';
                pill.style.color = 'var(--text)';
            }
        }

        saveAnswerLive(questionId, null, null, qIndex);
    }

    function confirmSubmit() {
        if (confirm('هل أنت متأكد من رغبتك في تسليم الاختبار الآن؟ لن تتمكن من تعديل الإجابات بعد التسليم.')) {
            document.getElementById('examForm').submit();
        }
    }
</script>
@endsection
