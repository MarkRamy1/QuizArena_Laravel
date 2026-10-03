@extends('layouts.app')

@section('title', __('app.quiz_results'))

@section('content')
<div style="max-width: 920px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    @php
        $passed = $attempt->percentage >= $attempt->quiz->passing_score;
    @endphp

    <!-- Score Summary Card -->
    <div class="card" style="text-align: center; padding: 36px; border-top: 6px solid {{ $passed ? 'var(--success)' : 'var(--danger)' }};">
        <div style="font-size: 54px; margin-bottom: 12px;">
            {{ $passed ? '🎉' : '📚' }}
        </div>

        <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 8px;">
            {{ $attempt->quiz->title }}
        </h1>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
            {{ __('app.student') }}: <strong>{{ $attempt->student->name }}</strong> &bull; تاريخ التسليم: {{ $attempt->submitted_at?->format('Y-m-d H:i') }}
        </p>

        <div style="display: inline-flex; align-items: center; justify-content: center; gap: 24px; background: var(--surface); padding: 18px 36px; border-radius: var(--radius-lg); border: 1px solid var(--surface-border); margin-bottom: 20px; flex-wrap: wrap;">
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">{{ __('app.score') }}</div>
                <div style="font-size: 32px; font-weight: 900; color: var(--primary);">
                    {{ $attempt->score }} <span style="font-size: 18px; color: var(--text-muted);">/ {{ $attempt->max_score }}</span>
                </div>
            </div>

            <div style="width: 1px; height: 40px; background: var(--surface-border);"></div>

            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">{{ __('app.percentage') }}</div>
                <div style="font-size: 32px; font-weight: 900; color: {{ $passed ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $attempt->percentage }}%
                </div>
            </div>

            <div style="width: 1px; height: 40px; background: var(--surface-border);"></div>

            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">{{ __('app.status') }}</div>
                <div style="font-size: 20px; font-weight: 900; color: {{ $passed ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $passed ? __('app.passed') : __('app.failed') }}
                </div>
            </div>
        </div>

        <!-- Printable PDF Certificate Banner for Passing Score -->
        @if($passed && $attempt->certificate)
        <div style="background: linear-gradient(135deg, rgba(245, 196, 81, 0.15), rgba(79, 70, 229, 0.1)); border: 2px solid rgba(245, 196, 81, 0.5); border-radius: var(--radius-md); padding: 20px; margin: 16px auto; max-width: 650px; text-align: center;">
            <div style="font-size: 36px; margin-bottom: 6px;">📜</div>
            <h3 style="font-size: 18px; font-weight: 900; color: #f59e0b; margin-bottom: 6px;">
                تهانينا! تم إصدار شهادة إنجاز رقمية معتمدة باسمك
            </h3>
            <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 14px;">
                كود التحقق الرقمي: <b style="font-family: monospace; color: var(--text-main);">{{ $attempt->certificate->verification_code }}</b>
            </p>
            <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('certificates.show', $attempt->certificate->id) }}" class="btn btn-primary" style="background: #c59b35; border-color: #b38928;">
                    🖨️ عرض وطباعة الشهادة PDF
                </a>
                <a href="{{ route('certificates.verify', $attempt->certificate->verification_code) }}" target="_blank" class="btn btn-sm">
                    🔍 صفحة التحقق العلنية
                </a>
            </div>
        </div>
        @endif

        <!-- Guardian Notification Confirmation -->
        @if($attempt->student->guardian_email || $attempt->student->guardian_phone)
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 12px 18px; margin: 14px auto; max-width: 650px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; font-size: 13px;">
            <div style="display: flex; align-items: center; gap: 10px; color: var(--success); font-weight: 700;">
                <span>👨‍👧‍👦</span>
                <span>تم إرسال إشعار فوري بنتيجة الاختبار لولي الأمر: {{ $attempt->student->guardian_name ?? 'ولي الأمر المسجل' }}</span>
            </div>
            <a href="{{ route('guardian.log') }}" class="btn btn-sm" style="font-size: 12px;">
                عرض سجل الإشعارات
            </a>
        </div>
        @endif

        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 14px;">
            <a href="{{ route('dashboard') }}" class="btn">
                🏠 {{ __('app.dashboard') }}
            </a>
            <a href="{{ route('quizzes.index') }}" class="btn">
                📝 بنك الاختبارات
            </a>
            @if($attempt->quiz->classroom_id)
            <a href="{{ route('classrooms.show', $attempt->quiz->classroom_id) }}" class="btn btn-primary">
                🏫 الفصل الدراسي
            </a>
            @endif
        </div>
    </div>

    <!-- AI Proctoring Anti-Cheating Report Card -->
    <div class="card" style="border-right: 5px solid {{ $attempt->proctor_violations_count > 0 ? 'var(--warning)' : 'var(--success)' }};">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
            <h2 style="font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <span>🛡️</span> تقرير نزاهة المراقبة الذكية (AI Proctor Report)
            </h2>
            <span class="role-pill" style="font-size: 12px; font-weight: 800; {{ $attempt->proctor_violations_count == 0 ? 'background: rgba(16,185,129,0.15); color: var(--success);' : 'background: rgba(239,68,68,0.15); color: var(--danger);' }}">
                @if($attempt->proctor_violations_count == 0)
                    🟢 اختبار نظيف ومكتمل بنزاهة تامة
                @else
                    ⚠️ تم تسجيل {{ $attempt->proctor_violations_count }} مخالفة سلوكية
                @endif
            </span>
        </div>

        <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 14px;">
            قام المراقب الذكي برصد جلسة الاختبار بالكامل، ومتابعة مغادرة الشاشة، وتبديل النوافذ، ومنع النسخ واللصق.
        </p>

        @php
            $logs = [];
            if ($attempt->proctor_log) {
                $logs = json_decode($attempt->proctor_log, true) ?? [];
            }
        @endphp

        @if(!empty($logs))
        <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 14px;">
            <div style="font-size: 12.5px; font-weight: 800; color: var(--danger); margin-bottom: 8px;">
                سجل المخالفات والتنبيهات المرصودة:
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($logs as $logItem)
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12.5px; padding: 6px 10px; background: rgba(239,68,68,0.08); border-radius: var(--radius-sm); border: 1px solid rgba(239,68,68,0.2);">
                    <span style="font-weight: 700; color: var(--danger);">
                        ⚠️ المخالفة {{ $logItem['strike'] ?? '' }}: {{ $logItem['description'] ?? $logItem['type'] }}
                    </span>
                    <span style="color: var(--text-muted); font-family: monospace;">{{ $logItem['time'] ?? '' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); border-radius: var(--radius-md); padding: 12px 16px; font-size: 13px; color: var(--success); font-weight: 700;">
            ✓ لم يتم تسجيل أي محاولات غش أو مغادرة لصفحة الاختبار طوال فترة الإجابة.
        </div>
        @endif
    </div>

    <!-- Question-by-Question Review -->
    <div class="card">
        <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <span>🔍</span> {{ __('app.review_quiz') }}
        </h2>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach($attempt->quiz->questions as $idx => $q)
            @php
                $ans = $attempt->answers->where('question_id', $q->id)->first();
                $isCorrect = $ans?->is_correct;
            @endphp
            <div style="background: var(--surface); border: 1px solid {{ $isCorrect ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.4)' }}; border-radius: var(--radius-md); padding: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-weight: 800; font-size: 14px;">
                        {{ __('app.question') }} {{ $idx + 1 }} &bull;
                        <span style="color: {{ $isCorrect ? 'var(--success)' : 'var(--danger)' }};">
                            {{ $isCorrect ? '✅ ' . __('app.correct_answer') : '❌ ' . __('app.incorrect_answer') }}
                        </span>
                    </span>
                    <span class="role-pill">{{ $ans?->points_awarded ?? 0 }} / {{ $q->points }} {{ __('app.points') }}</span>
                </div>

                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; line-height: 1.5;">
                    {{ $q->question_text }}
                </h4>

                @if($q->type === 'CHOOSE' || $q->type === 'TRUE_FALSE')
                <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px;">
                    @foreach($q->options as $opt)
                    @php
                        $isSelected = ($ans?->selected_option_id === $opt->id);
                        $isOptCorrect = $opt->is_correct;
                    @endphp
                    <div style="padding: 10px 14px; border-radius: var(--radius-sm); font-size: 14px; display: flex; align-items: center; justify-content: space-between; border: 1px solid {{ $isOptCorrect ? 'var(--success)' : ($isSelected ? 'var(--danger)' : 'var(--surface-border)') }}; background: {{ $isOptCorrect ? 'rgba(16,185,129,0.1)' : ($isSelected ? 'rgba(239,68,68,0.1)' : 'transparent') }};">
                        <span>{{ $opt->option_text }}</span>
                        @if($isSelected && $isOptCorrect)
                        <span style="color: var(--success); font-weight: 700; font-size: 12px;">إجابتك (صحيحة) ✓</span>
                        @elseif($isSelected && !$isOptCorrect)
                        <span style="color: var(--danger); font-weight: 700; font-size: 12px;">إجابتك (خاطئة) ✗</span>
                        @elseif($isOptCorrect)
                        <span style="color: var(--success); font-weight: 700; font-size: 12px;">الإجابة النموذجية الصحيحة ✓</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif($q->type === 'COMPLETE')
                <div style="font-size: 14px; margin-bottom: 10px;">
                    <div>إجابتك: <b style="color: {{ $isCorrect ? 'var(--success)' : 'var(--danger)' }};">{{ $ans?->student_answer ?: 'لم يتم الإجابة' }}</b></div>
                    <div style="color: var(--text-muted); font-size: 13px;">الإجابة النموذجية: <b style="color: var(--accent);">{{ $q->model_answer }}</b></div>
                </div>
                @elseif($q->type === 'ESSAY')
                <div style="font-size: 14px; margin-bottom: 10px;">
                    <div style="font-weight: 700; margin-bottom: 4px;">إجابتك المكتوبة:</div>
                    <div style="background: var(--bg); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); font-size: 13.5px; line-height: 1.6;">
                        {{ $ans?->student_answer ?: 'لم يتم تقديم إجابة.' }}
                    </div>
                </div>
                @endif

                @if($q->explanation)
                <div style="font-size: 12.5px; color: var(--text-muted); background: var(--bg); padding: 8px 12px; border-radius: var(--radius-sm); margin-top: 8px;">
                    💡 <strong>توضيح الإجابة:</strong> {{ $q->explanation }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
