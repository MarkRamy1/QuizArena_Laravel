@extends('layouts.app')

@section('title', 'الاختبار الحي التفاعلي — Live Quiz Arena')

@section('content')
<div style="display: flex; flex-direction: column; gap: 26px;">

    <!-- Live Arena Hero Header -->
    <div class="card" style="padding: 32px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(6, 182, 212, 0.15)), var(--bg-elevated); border: 1px solid var(--primary); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="hero-badge" style="margin-bottom: 8px;">
                    ⚡ Real-Time Competitive Arena
                </span>
                <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 6px;">
                    الاختبار الحي التفاعلي (Live Quiz)
                </h1>
                <p style="color: var(--text-muted); font-size: 14.5px;">
                    تنافس في الوقت الفعلي مع زملائك، اكسب نقاطاً مضاعفة بناءً على دقة وسرعة إجابتك، وتصدر منصة التتويج!
                </p>
            </div>

            <!-- PIN Join Input -->
            <div style="background: var(--bg-elevated); border: 2px solid var(--primary); border-radius: var(--radius-lg); padding: 18px 22px; width: min(380px, 100%);">
                <div style="font-weight: 800; font-size: 14px; margin-bottom: 8px; color: var(--accent);">
                    🔑 انضم لجلسة حية بواسطة رمز PIN:
                </div>
                <form action="{{ route('live-quiz.join') }}" method="POST" style="display: flex; gap: 8px;">
                    @csrf
                    <input type="text"
                           name="pin"
                           placeholder="أدخل رمز PIN المكون من 6 أرقام..."
                           class="form-control"
                           style="font-size: 16px; font-weight: 800; letter-spacing: 2px; text-align: center;"
                           maxlength="6"
                           required>
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                        دخول ⚡
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(Auth::user()->isTeacher() || Auth::user()->isAdmin())
    <!-- Teacher Section: Start a Live Quiz from an Existing Quiz -->
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>👨‍🏫</span> بدء جلسة حية جديدة من اختباراتك (Host a Live Quiz)
        </h3>
        <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 18px;">
            اختر أي اختبار من اختباراتك المنشورة لتحويله إلى جلسة منافسة حية فورية وتوليد رمز PIN للطلاب:
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px;">
            @forelse($myQuizzes as $quiz)
            <div style="padding: 16px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-weight: 800; font-size: 14.5px; margin-bottom: 4px;">{{ $quiz->title }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                        الكود: <b>{{ $quiz->code }}</b> &bull; {{ $quiz->questions->count() }} أسئلة &bull; {{ $quiz->duration_minutes }} دقيقة
                    </div>
                </div>

                <form action="{{ route('live-quiz.start', $quiz->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        ⚡ بدء جلسة Live وتوليد PIN
                    </button>
                </form>
            </div>
            @empty
            <p style="color: var(--text-muted);">لا توجد اختبارات متاحة حالياً لبدء جلسة حية.</p>
            @endforelse
        </div>
    </div>
    @endif

    <!-- Active Live Sessions List -->
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>🔴</span> الجلسات الحية النشطة حالياً
        </h3>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($activeSessions as $s)
            @php $count = count($s->participants ?? []); @endphp
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <span style="font-size: 24px; animation: pulse 2s infinite;">🔴</span>
                    <div>
                        <div style="font-weight: 800; font-size: 15px;">{{ $s->quiz->title }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            المعلم: {{ $s->teacher->name }} &bull; رمز PIN: <b style="color: var(--accent); font-size: 14px;">{{ $s->pin_code }}</b> &bull; المشاركون: {{ $count }} طالب
                        </div>
                    </div>
                </div>

                <a href="{{ route('live-quiz.room', $s->id) }}" class="btn btn-primary">
                    دخول الجلسة ⚡
                </a>
            </div>
            @empty
            <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                <div style="font-size: 36px; margin-bottom: 8px;">⚡</div>
                <div>لا توجد جلسات اختبار حية جارية في هذه اللحظة.</div>
                <div style="font-size: 12px; margin-top: 4px;">يمكن للمعلم بدء جلسة من القائمة أعلاه ومشاركة رمز PIN مع الطلاب.</div>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
