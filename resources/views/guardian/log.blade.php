@extends('layouts.app')

@section('title', 'سجل إشعارات وتنبيهات أولياء الأمور')

@section('content')
<div style="max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>👨‍👧‍👦</span> سجل تنبيهات أولياء الأمور
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">
                كافة الإشعارات المرسلة لولي الأمر بخصوص نتائج الاختبارات والغياب عن الحصص والمتابعة الأكاديمية
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-sm">
            &larr; {{ __('app.dashboard') }}
        </a>
    </div>

    @if($notifications->isEmpty())
    <div class="card" style="text-align: center; padding: 48px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 6px;">لا توجد إشعارات مرسلة حتى الآن</h3>
        <p style="font-size: 13.5px;">يتم إرسال إشعارات تلقائية لولي الأمر فور تسليم أي اختبار أو عند تسجيل غياب عن حصة أو اختبار.</p>
    </div>
    @else
    <div style="display: flex; flex-direction: column; gap: 14px;">
        @foreach($notifications as $notif)
        <div class="card" style="border-right: 5px solid {{ $notif->type === 'EXAM_SCORE' ? 'var(--success)' : ($notif->type === 'EXAM_MISSED' ? 'var(--danger)' : 'var(--warning)') }};">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span class="role-pill" style="font-size: 11px;">
                            @if($notif->type === 'EXAM_SCORE') 🏆 نتيجة اختبار
                            @elseif($notif->type === 'SESSION_MISSED') ⚠️ غياب عن حصة
                            @elseif($notif->type === 'EXAM_MISSED') 🚨 غياب عن اختبار
                            @else 📢 تنبيه عام
                            @endif
                        </span>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $notif->sent_at?->diffForHumans() }} ({{ $notif->sent_at?->format('Y-m-d H:i') }})</span>
                    </div>

                    <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main);">
                        {{ $notif->title }}
                    </h3>
                </div>

                <div style="text-align: left; font-size: 12px; color: var(--text-muted);">
                    <div>الطالب: <strong style="color: var(--text-main);">{{ $notif->student->name }}</strong></div>
                    <div>ولي الأمر: <strong>{{ $notif->guardian_name ?? 'ولي الأمر' }}</strong></div>
                    @if($notif->guardian_email)<div>البريد: <code>{{ $notif->guardian_email }}</code></div>@endif
                    @if($notif->guardian_phone)<div>الهاتف: <code>{{ $notif->guardian_phone }}</code></div>@endif
                </div>
            </div>

            <div style="background: var(--surface); padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; line-height: 1.7; white-space: pre-line; border: 1px solid var(--surface-border);">
                {{ $notif->message }}
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px; font-size: 12px; color: var(--text-muted);">
                <div>
                    @if($notif->classroom)
                    <span>الفصل: <strong>{{ $notif->classroom->name }}</strong> &bull;</span>
                    @endif
                    @if($notif->quiz)
                    <span>الاختبار: <strong>{{ $notif->quiz->title }}</strong></span>
                    @endif
                </div>
                <div style="display: flex; align-items: center; gap: 6px; color: var(--success); font-weight: 700;">
                    <span>✓ تم الإرسال والإشعار بنجاح</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $notifications->links() }}
    @endif

</div>
@endsection
