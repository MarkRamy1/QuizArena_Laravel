@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div style="max-width: 950px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <!-- Back Navigation -->
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ $quiz->classroom ? route('classrooms.show', $quiz->classroom) : route('quizzes.index') }}" class="btn btn-sm">
            &larr; {{ $quiz->classroom ? $quiz->classroom->name : __('app.quizzes') }}
        </a>
        <div style="display: flex; align-items: center; gap: 8px;">
            @if($quiz->is_ai_proctored)
            <span class="role-pill" style="background: rgba(16,185,129,0.15); color: var(--success); font-size: 11px;">
                🛡️ مراقبة AI نشطة
            </span>
            @endif
            <span class="role-pill">{{ $quiz->code }}</span>
        </div>
    </div>

    <!-- Overview Card -->
    <div class="card" style="border-top: 4px solid var(--primary);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 6px;">{{ $quiz->title }}</h1>
                <p style="color: var(--text-muted); font-size: 13.5px;">
                    👨‍🏫 {{ $quiz->teacher->name }} @if($quiz->classroom) &bull; 🏫 {{ $quiz->classroom->name }} @endif
                </p>
            </div>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="background: var(--surface); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('app.duration_minutes') }}</div>
                    <div style="font-size: 19px; font-weight: 900; color: var(--accent);">{{ $quiz->duration_minutes }} دقيقة</div>
                </div>

                @if($quiz->has_per_question_timer)
                <div style="background: var(--surface); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">وقت كل سؤال</div>
                    <div style="font-size: 19px; font-weight: 900; color: var(--primary-light);">{{ $quiz->per_question_seconds ?: 'محدد' }} ثانية</div>
                </div>
                @endif

                <div style="background: var(--surface); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('app.passing_score') }}</div>
                    <div style="font-size: 19px; font-weight: 900; color: var(--success);">{{ $quiz->passing_score }}%</div>
                </div>
            </div>
        </div>

        @if($quiz->description)
        <p style="font-size: 14.5px; color: var(--text-main); line-height: 1.7; margin-bottom: 20px;">
            {{ $quiz->description }}
        </p>
        @endif

        <!-- Attached PDF Exam File -->
        @if($quiz->pdf_exam_path)
        <div style="background: var(--surface); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 26px;">📄</span>
                <div>
                    <div style="font-weight: 800; font-size: 14px;">ورقة الاختبار الرسمية بصيغة PDF</div>
                    <div style="font-size: 12px; color: var(--text-muted);">{{ $quiz->pdf_exam_name ?? 'ورقة_الامتحان.pdf' }}</div>
                </div>
            </div>
            <a href="{{ route('quizzes.downloadPdf', $quiz) }}" target="_blank" class="btn btn-sm btn-accent">
                ⬇️ تحميل وقراءة ورقة الاختبار PDF
            </a>
        </div>
        @endif

        <div style="display: flex; gap: 20px; font-size: 13.5px; color: var(--text-muted); margin-bottom: 24px; flex-wrap: wrap;">
            <span>❓ {{ $quiz->questions->count() }} {{ __('app.questions') }}</span>
            <span>💯 {{ $quiz->questions->sum('points') }} {{ __('app.points') }}</span>
            <span>📜 شهادة معتمدة تلقائية عند تحقيق {{ $quiz->passing_score }}% فأعلى</span>
            <span>👨‍👧‍👦 إشعار فوري لولي الأمر بالدرجة فور التسليم</span>
        </div>

        <!-- QR Code & Direct Join Box (Requirement 19) -->
        <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode(route('quizzes.show', $quiz->id)) }}"
                     alt="Quiz QR Code"
                     style="width: 80px; height: 80px; border-radius: 8px; background: #fff; padding: 4px; border: 1px solid var(--surface-border);">
                <div>
                    <div style="font-weight: 800; font-size: 15px; margin-bottom: 2px;">رمز الاستجابة السريعة (Quiz QR Code)</div>
                    <div style="font-size: 12.5px; color: var(--text-muted); line-height: 1.5;">
                        امسح الرمز بكاميرا الهاتف للدخول المباشر، أو شارك كود الاختبار: <b style="color: var(--accent); font-family: monospace; font-size: 15px;">{{ $quiz->code }}</b>
                    </div>
                </div>
            </div>

            @if($isTeacher)
            <a href="{{ route('quizzes.exportResults', $quiz->id) }}" class="btn btn-sm" title="تصدير تقرير النتائج Excel">
                📊 تصدير الدرجات Excel (CSV)
            </a>
            @endif
        </div>

        @if(!$isTeacher)
        <form action="{{ route('quizzes.start', $quiz) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" style="padding: 14px 36px; font-size: 17px; width: 100%;">
                🚀 {{ __('app.start_quiz') }}
            </button>
        </form>
        @endif
    </div>

    <!-- Student Previous Attempts Section -->
    @if(!$isTeacher && $myAttempts->isNotEmpty())
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">
            📜 محاولاتي السابقة في هذا الاختبار ({{ $myAttempts->count() }})
        </h3>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($myAttempts as $att)
            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--surface); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong>{{ $att->submitted_at?->format('Y-m-d H:i') ?? 'قيد الحل' }}</strong>
                    <div style="font-size: 13px; color: var(--text-muted);">
                        الدرجة: {{ $att->score }} / {{ $att->max_score }}
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="role-pill" style="color: {{ $att->percentage >= $quiz->passing_score ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $att->percentage }}%
                    </span>

                    @if($att->certificate)
                    <a href="{{ route('certificates.show', $att->certificate->id) }}" class="btn btn-sm" style="background: rgba(245,196,81,0.15); color: #f59e0b; border: 1px solid rgba(245,196,81,0.4);">
                        📜 الشهادة
                    </a>
                    @endif

                    <a href="{{ route('quizzes.results', $att) }}" class="btn btn-sm btn-primary">
                        {{ __('app.review_quiz') }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Teacher Attempts Review Section -->
    @if($isTeacher)
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <h2 style="font-size: 19px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> نتائج ومحاولات الطلاب ({{ $attempts->count() }})
            </h2>
            <a href="{{ route('guardian.log') }}" class="btn btn-sm">
                👨‍👧‍👦 سجل تنبيهات أولياء الأمور
            </a>
        </div>

        @if($attempts->isEmpty())
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">⏱️</div>
            <p>لم يقم أي طالب بتقديم هذا الاختبار بعد.</p>
        </div>
        @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($attempts as $att)
            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--surface); padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="{{ $att->student->avatar_url }}" alt="{{ $att->student->name }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <div style="font-weight: 800; font-size: 15px;">{{ $att->student->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            {{ $att->submitted_at?->format('Y-m-d H:i') }} &bull; الدرجة: {{ $att->score }}/{{ $att->max_score }}
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- Proctoring Pill -->
                    <span class="role-pill" style="font-size: 11.5px; {{ $att->proctor_violations_count > 0 ? 'background: rgba(239,68,68,0.15); color: var(--danger);' : 'background: rgba(16,185,129,0.15); color: var(--success);' }}">
                        🛡️ {{ $att->proctor_violations_count > 0 ? $att->proctor_violations_count . ' مخالفات رُصدت' : 'مراقبة نظيفة' }}
                    </span>

                    <!-- Percentage Pill -->
                    <span class="role-pill" style="color: {{ $att->percentage >= $quiz->passing_score ? 'var(--success)' : 'var(--danger)' }}; font-weight: 800;">
                        {{ $att->percentage }}%
                    </span>

                    @if($att->certificate)
                    <a href="{{ route('certificates.show', $att->certificate->id) }}" target="_blank" class="btn btn-sm" style="color: #f59e0b; border-color: rgba(245,196,81,0.4);" title="عرض الشهادة الممنوحة">
                        📜 الشهادة
                    </a>
                    @endif

                    <a href="{{ route('quizzes.results', $att) }}" class="btn btn-sm btn-primary">
                        {{ __('app.review_quiz') }}
                    </a>

                    <!-- Teacher Absence Alert Button to Guardian -->
                    <form action="{{ route('guardian.alert') }}" method="POST" style="display: inline;" onsubmit="return confirm('هل تريد إرسال تنبيه رسمي لولي أمر الطالب؟');">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $att->student_id }}">
                        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                        <input type="hidden" name="classroom_id" value="{{ $quiz->classroom_id }}">
                        <input type="hidden" name="type" value="CUSTOM">
                        <input type="hidden" name="custom_note" value="متابعة أداء الطالب في اختبار ({{ $quiz->title }})">
                        <button type="submit" class="btn btn-sm" style="font-size: 11px;" title="إرسال رسالة تنبيه لولي الأمر">
                            👨‍👧‍👦 إشعار ولي الأمر
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
