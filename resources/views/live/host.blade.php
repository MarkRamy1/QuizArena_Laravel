@extends('layouts.app')

@section('title', 'شاشة المعلم — جلسة الاختبار الحي Live Host')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px; max-width: 1000px; margin: auto;">

    <!-- PIN Banner Card -->
    <div class="card" style="text-align: center; padding: 40px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(6, 182, 212, 0.2)), var(--bg-elevated); border: 2px solid var(--primary);">
        <div style="font-size: 14px; font-weight: 800; color: var(--accent); margin-bottom: 8px;">
            🔴 جلسة اختبار حية نشطة (LIVE ARENA HOST)
        </div>
        <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 12px;">{{ $session->quiz->title }}</h1>
        <p style="color: var(--text-muted); font-size: 14.5px; margin-bottom: 24px;">
            اطلب من الطلاب الدخول إلى <b>{{ url('/live-quiz') }}</b> وإدخال رمز PIN التالي:
        </p>

        <!-- Huge PIN Display -->
        <div style="display: inline-flex; align-items: center; justify-content: center; background: var(--surface); border: 3px solid var(--accent); border-radius: var(--radius-xl); padding: 16px 48px; font-size: 48px; font-weight: 900; letter-spacing: 6px; color: #fff; box-shadow: 0 8px 30px rgba(6, 182, 212, 0.35);">
            {{ $session->pin_code }}
        </div>

        <div style="margin-top: 20px; font-size: 13.5px; color: var(--text-muted);">
            عدد المشاركين الحالي: <b id="participantCount" style="color: var(--success); font-size: 18px;">{{ count($session->participants ?? []) }}</b> طالب
        </div>
    </div>

    <!-- Live Questions & Controls Card -->
    <div class="card" style="padding: 30px;">
        @php
            $questions = $session->quiz->questions;
            $currentQ = $questions->first();
        @endphp

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <span class="role-pill" id="questionBadge">السؤال 1 من {{ $questions->count() }}</span>
                <h3 id="questionText" style="font-size: 20px; font-weight: 900; margin-top: 6px;">
                    {{ $currentQ->question_text ?? 'لا توجد أسئلة مضافة' }}
                </h3>
            </div>

            <!-- Countdown Timer Circle -->
            <div style="width: 64px; height: 64px; border-radius: 50%; border: 4px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 900; color: #fff;" id="timerDisplay">
                20
            </div>
        </div>

        <!-- Simulated Live Stats Bar -->
        <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 8px;">
                <span style="color: var(--success);">الإجابات الصحيحة: 75%</span>
                <span style="color: var(--danger);">الإجابات الخاطئة: 25%</span>
            </div>
            <div style="height: 12px; background: var(--danger); border-radius: 999px; overflow: hidden; display: flex;">
                <div style="width: 75%; background: var(--success); height: 100%;"></div>
            </div>
        </div>

        <!-- Participants Lobby Strip -->
        <h4 style="font-size: 15px; font-weight: 800; margin-bottom: 12px;">المشاركون المتصلون بالساحة:</h4>
        <div id="participantsList" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">
            @foreach($session->participants ?? [] as $p)
            <div style="display: flex; align-items: center; gap: 8px; background: var(--surface); border: 1px solid var(--surface-border); padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 700;">
                <img src="{{ $p['avatar'] ?? 'https://ui-avatars.com/api/?name=Student' }}" style="width: 22px; height: 22px; border-radius: 50%;">
                <span>{{ $p['name'] }}</span>
                <span style="color: var(--accent); font-weight: 900;">({{ $p['score'] ?? 0 }} pts)</span>
            </div>
            @endforeach
        </div>

        <!-- Control Buttons -->
        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="btn btn-primary" onclick="nextQuestion()" style="padding: 12px 28px; font-size: 15px;">
                السؤال التالي ➡
            </button>
            <a href="{{ route('live-quiz.index') }}" class="btn btn-danger" onclick="return confirm('هل تريد إنهاء الجلسة الحية؟');">
                إنهاء الجلسة 🛑
            </a>
        </div>
    </div>

</div>

<script>
    let timeLeft = 20;
    const timerElem = document.getElementById('timerDisplay');
    const interval = setInterval(() => {
        if (timeLeft > 0) {
            timeLeft--;
            timerElem.innerText = timeLeft;
            if (timeLeft <= 5) {
                timerElem.style.borderColor = 'var(--danger)';
                timerElem.style.color = 'var(--danger)';
            }
        }
    }, 1000);

    const questionsData = @json($session->quiz->questions);
    let currentIdx = 0;

    function nextQuestion() {
        currentIdx++;
        if (currentIdx < questionsData.length) {
            document.getElementById('questionBadge').innerText = `السؤال ${currentIdx + 1} من ${questionsData.length}`;
            document.getElementById('questionText').innerText = questionsData[currentIdx].question_text;
            timeLeft = 20;
            timerElem.innerText = timeLeft;
            timerElem.style.borderColor = 'var(--primary)';
            timerElem.style.color = '#fff';
        } else {
            alert('انتهت كافة أسئلة الجلسة الحية! تم تتويج المتصدرين.');
        }
    }
</script>
@endsection
