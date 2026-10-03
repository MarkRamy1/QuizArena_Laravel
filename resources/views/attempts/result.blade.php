@extends('layouts.app')

@section('title', 'نتيجة الاختبار — ' . $attempt->quiz->title)

@section('content')
<div style="max-width: 900px; margin: auto;">
    <!-- Result Header Card -->
    <div class="card" style="text-align: center; padding: 40px 24px; margin-bottom: 24px;">
        <div style="font-size: 54px; margin-bottom: 10px;">
            {{ $passed ? '🏆' : '📚' }}
        </div>

        <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 6px;">
            {{ $passed ? 'تهانينا! لقد اجتزت الاختبار بنجاح 🎉' : 'حظاً أوفر في المحاولات القادمة 💪' }}
        </h1>
        <p style="color: var(--muted); font-size: 15px; margin-bottom: 24px;">
            اختبار: <b style="color: var(--text);">{{ $attempt->quiz->title }}</b> &bull; كود: <span style="font-family: monospace; color: var(--secondary);">{{ $attempt->quiz->code }}</span>
        </p>

        <!-- Big Score Circular Display -->
        <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 150px; height: 150px; border-radius: 50%; background: radial-gradient(circle, var(--card2), var(--card)); border: 6px solid {{ $passed ? 'var(--success)' : 'var(--danger)' }}; box-shadow: 0 0 35px {{ $passed ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)' }}; margin-bottom: 24px;">
            <div style="font-size: 38px; font-weight: 900; color: {{ $passed ? '#4ade80' : '#f87171' }};">
                {{ $attempt->percentage }}%
            </div>
            <div style="font-size: 12px; color: var(--muted); font-weight: 700;">
                {{ $attempt->score }} من {{ $attempt->max_score }} نقطة
            </div>
        </div>

        <!-- Metrics Row -->
        <div class="grid4" style="margin-bottom: 24px;">
            <div class="card-inner">
                <div style="font-size: 12px; color: var(--muted);">حالة التقييم</div>
                <div style="font-size: 16px; font-weight: 800; color: {{ $passed ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $passed ? 'ناجح ومجتاز 🎯' : 'لم يجتز' }}
                </div>
            </div>

            <div class="card-inner">
                <div style="font-size: 12px; color: var(--muted);">درجة النجاح المطلوبة</div>
                <div style="font-size: 16px; font-weight: 800; color: var(--text);">
                    {{ $attempt->quiz->passing_score }}%
                </div>
            </div>

            <div class="card-inner">
                <div style="font-size: 12px; color: var(--muted);">الوقت المستغرق</div>
                <div style="font-size: 16px; font-weight: 800; color: var(--text);">
                    {{ floor($attempt->duration_seconds / 60) }} دقيقة و {{ $attempt->duration_seconds % 60 }} ثانية
                </div>
            </div>

            <div class="card-inner">
                <div style="font-size: 12px; color: var(--muted);">نقاط الـ XP المكتسبة</div>
                <div style="font-size: 16px; font-weight: 800; color: #b9b2ff;">
                    +{{ $attempt->percentage >= 90 ? '75' : ($attempt->percentage >= 70 ? '50' : '25') }} XP 💎
                </div>
            </div>
        </div>

        <!-- Certificate Earned Callout -->
        @if($attempt->certificate)
        <div style="background: linear-gradient(135deg, rgba(245, 196, 81, 0.15), rgba(109, 93, 252, 0.1)); border: 2px solid rgba(245, 196, 81, 0.4); border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="text-align: right;">
                <div style="font-size: 16px; font-weight: 900; color: #f5c451;">
                    🎓 صدرت لك شهادة إنجاز معتمدة!
                </div>
                <div style="font-size: 13px; color: var(--muted); margin-top: 2px;">
                    كود التحقق الرقمي: <b style="font-family: monospace; color: var(--text);">{{ $attempt->certificate->verification_code }}</b>
                </div>
            </div>
            <a href="{{ route('certificates.show', $attempt->certificate->id) }}" class="btn warning" style="padding: 10px 22px;">
                عرض وتحميل الشهادة 📜
            </a>
        </div>
        @endif

        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('quizzes.index') }}" class="btn">
                &rarr; العودة لبنك الاختبارات
            </a>
            <a href="{{ route('dashboard') }}" class="btn primary">
                الذهاب للوحة التحكم 🚀
            </a>
        </div>
    </div>

    <!-- Detailed Answers Review -->
    <div class="card" style="padding: 28px;">
        <h3 style="font-size: 20px; font-weight: 900; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <span>🔍</span> مراجعة وتدقيق الإجابات بالتفصيل
        </h3>

        <div style="display: flex; flex-direction: column; gap: 18px;">
            @foreach($attempt->quiz->questions as $idx => $q)
            @php
                $ans = $answersMap->get($q->id);
                $isCorrect = $ans ? $ans->is_correct : false;
            @endphp
            <div class="card-inner" style="border-right: 4px solid {{ $isCorrect ? 'var(--success)' : 'var(--danger)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: 800; font-size: 14px;">السؤال {{ $idx + 1 }}</span>
                    <span class="pill-stat" style="font-size: 12px; color: {{ $isCorrect ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $isCorrect ? 'إجابة صحيحة ✓' : 'إجابة غير صحيحة ✗' }} (+{{ $ans ? $ans->points_awarded : 0 }}/{{ $q->points }})
                    </span>
                </div>

                <div style="font-size: 16px; font-weight: 700; margin-bottom: 12px;">
                    {{ $q->text }}
                </div>

                @if($q->type === 'MCQ_SINGLE')
                <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; font-size: 14px;">
                    @foreach($q->choices as $choice)
                    @php
                        $isSelected = $ans && $ans->choice_id == $choice->id;
                        $isRightChoice = $choice->is_correct;
                    @endphp
                    <div style="padding: 10px 14px; border-radius: 8px; border: 1px solid var(--line);
                        @if($isRightChoice) background: rgba(34, 197, 94, 0.15); border-color: var(--success); color: #4ade80; font-weight: 700;
                        @elseif($isSelected && !$isRightChoice) background: rgba(239, 68, 68, 0.15); border-color: var(--danger); color: #f87171;
                        @else background: var(--card2); opacity: 0.75;
                        @endif">
                        <span>{{ $choice->text }}</span>
                        @if($isRightChoice) <b style="float: left;">(الإجابة النموذجية الصحيحة ✓)</b> @endif
                        @if($isSelected && !$isRightChoice) <b style="float: left;">(إجابتك المختارة ✗)</b> @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div style="font-size: 14px; margin-bottom: 10px;">
                    <div style="color: var(--muted);">إجابتك المسجلة: <b style="color: var(--text);">{{ $ans?->text_answer ?? 'لم تتم الإجابة' }}</b></div>
                    <div style="color: var(--success); margin-top: 4px;">الإجابة النموذجية: <b style="color: var(--text);">{{ $q->model_answer }}</b></div>
                </div>
                @endif

                @if($q->explanation)
                <div style="font-size: 13px; color: var(--muted); background: rgba(0,217,255,0.06); padding: 10px 14px; border-radius: 8px; border-right: 3px solid var(--secondary); margin-top: 8px;">
                    💡 <b>التفسير التعليمي:</b> {{ $q->explanation }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
