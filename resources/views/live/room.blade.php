@extends('layouts.app')

@section('title', 'غرفة المنافسة الحية — Live Arena')

@section('content')
<div style="max-width: 850px; margin: auto; display: flex; flex-direction: column; gap: 20px;">

    <!-- Live Header Bar -->
    <div class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: var(--bg-elevated); border-color: var(--primary);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px; animation: pulse 1.5s infinite;">🔴</span>
            <div>
                <div style="font-weight: 800; font-size: 15px;">{{ $session->quiz->title }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">جلسة حية &bull; رمز PIN: <b>{{ $session->pin_code }}</b></div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="text-align: center;">
                <div style="font-size: 11px; color: var(--text-muted);">نقاطك الحالية</div>
                <div id="myLiveScore" style="font-size: 20px; font-weight: 900; color: var(--accent);">0</div>
            </div>

            <div style="width: 50px; height: 50px; border-radius: 50%; border: 3px solid var(--accent); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900;" id="liveTimer">
                15
            </div>
        </div>
    </div>

    <!-- Active Question Card -->
    @php
        $questions = $session->quiz->questions()->with('options')->get();
        $q = $questions->first();
    @endphp

    <div class="card" id="questionCard" style="padding: 32px; text-align: center;">
        <span class="role-pill" id="qNumberBadge" style="margin-bottom: 12px; display: inline-block;">السؤال 1 من {{ $questions->count() }}</span>
        <h2 id="liveQText" style="font-size: 22px; font-weight: 900; margin-bottom: 28px; line-height: 1.5;">
            {{ $q->question_text ?? 'السؤال الأول في الجلسة الحية' }}
        </h2>

        <!-- Interactive Choice Grid -->
        <div id="choicesGrid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            @if($q && $q->options->count() > 0)
                @foreach($q->options as $opt)
                <button type="button"
                        class="btn choice-btn"
                        onclick="submitChoice({{ $opt->is_correct ? 'true' : 'false' }}, this)"
                        style="padding: 20px; font-size: 16px; font-weight: 800; border-radius: var(--radius-lg); text-align: center;">
                    {{ $opt->option_text }}
                </button>
                @endforeach
            @else
                <button type="button" class="btn choice-btn" onclick="submitChoice(true, this)" style="padding: 20px; font-size: 16px; font-weight: 800;">صح (True)</button>
                <button type="button" class="btn choice-btn" onclick="submitChoice(false, this)" style="padding: 20px; font-size: 16px; font-weight: 800;">خطأ (False)</button>
            @endif
        </div>

        <!-- Result Feedback Banner -->
        <div id="resultFeedback" style="display: none; margin-top: 24px; padding: 18px; border-radius: var(--radius-md); font-weight: 800; font-size: 16px;">
        </div>
    </div>

    <!-- Live Podium Summary (Shown when finished) -->
    <div id="podiumCard" class="card" style="display: none; text-align: center; padding: 40px;">
        <div style="font-size: 54px; margin-bottom: 8px;">🏆</div>
        <h2 style="font-size: 26px; font-weight: 900; margin-bottom: 8px;">انتهت الجولة الحية!</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">نتائج وتتويج أبطال الساحة:</p>

        <div style="display: flex; justify-content: center; gap: 18px; align-items: flex-end; margin-bottom: 30px;">
            <div style="background: rgba(148, 163, 184, 0.15); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 16px; width: 140px;">
                <div style="font-size: 28px;">🥈</div>
                <div style="font-weight: 800; font-size: 14px;">أحمد محمود</div>
                <div style="font-size: 12px; color: var(--accent);">420 pts</div>
            </div>

            <div style="background: linear-gradient(180deg, rgba(245, 158, 11, 0.25), rgba(245, 158, 11, 0.05)); border: 2px solid var(--warning); border-radius: var(--radius-lg); padding: 24px 16px; width: 160px; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);">
                <div style="font-size: 38px;">👑</div>
                <div style="font-weight: 900; font-size: 16px; color: #fbbf24;">نور الهدى</div>
                <div style="font-size: 14px; font-weight: 800; color: #fff;">580 pts</div>
            </div>

            <div style="background: rgba(180, 83, 9, 0.15); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 14px; width: 130px;">
                <div style="font-size: 24px;">🥉</div>
                <div style="font-weight: 800; font-size: 13px;">طارق عبد الله</div>
                <div style="font-size: 12px; color: var(--accent);">310 pts</div>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 12px 28px;">
            العودة للوحة التحكم 🚀
        </a>
    </div>

</div>

<script>
    let remainingSeconds = 15;
    let myScore = 0;
    let answered = false;
    const timerElem = document.getElementById('liveTimer');

    const countdown = setInterval(() => {
        if (remainingSeconds > 0 && !answered) {
            remainingSeconds--;
            timerElem.innerText = remainingSeconds;
        } else if (remainingSeconds <= 0 && !answered) {
            submitChoice(false, null);
        }
    }, 1000);

    function submitChoice(isCorrect, btn) {
        if (answered) return;
        answered = true;

        const allBtns = document.querySelectorAll('.choice-btn');
        allBtns.forEach(b => b.disabled = true);

        const feedback = document.getElementById('resultFeedback');
        feedback.style.display = 'block';

        if (isCorrect) {
            const points = 100 + (remainingSeconds * 10);
            myScore += points;
            document.getElementById('myLiveScore').innerText = myScore;

            if (btn) btn.style.background = 'var(--success)';
            feedback.style.background = 'rgba(16, 185, 129, 0.15)';
            feedback.style.border = '1px solid var(--success)';
            feedback.style.color = '#34d399';
            feedback.innerHTML = `✅ إجابة صحيحة وسريعة! كسبت +${points} نقطة خبرة!`;
        } else {
            if (btn) btn.style.background = 'var(--danger)';
            feedback.style.background = 'rgba(239, 68, 68, 0.15)';
            feedback.style.border = '1px solid var(--danger)';
            feedback.style.color = '#f87171';
            feedback.innerHTML = `❌ إجابة غير صحيحة أو نفد الوقت!`;
        }

        // Notify server
        fetch("{{ route('live-quiz.submitLiveAnswer', $session->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ is_correct: isCorrect, time_remaining: remainingSeconds })
        });

        // Show podium after delay
        setTimeout(() => {
            document.getElementById('questionCard').style.display = 'none';
            document.getElementById('podiumCard').style.display = 'block';
        }, 3500);
    }
</script>
@endsection
