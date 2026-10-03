@extends('layouts.app')

@section('title', 'البلاغات والملاحظات — Feedback & Problem Reports')

@section('content')
<div style="max-width: 850px; margin: auto; display: flex; flex-direction: column; gap: 22px;">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>🚨</span> سجل البلاغات والدعم الأكاديمي
            </h1>
            <p style="color: var(--text-muted); font-size: 13.5px;">تابع حالة البلاغات التي قدمتها حول أي أسئلة أو ملاحظات دراسية</p>
        </div>

        <a href="{{ route('feedback.create') }}" class="btn btn-primary">
            ➕ تقديم بلاغ أو استفسار جديد
        </a>
    </div>

    <div class="card">
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($reports as $r)
            <div style="padding: 16px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="role-pill">{{ $r->type }}</span>
                        <h4 style="font-size: 14.5px; font-weight: 800;">{{ $r->title }}</h4>
                    </div>

                    @if($r->status === 'RESOLVED')
                    <span class="role-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                        تم الحل والمراجعة ✓
                    </span>
                    @else
                    <span class="role-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                        قيد المراجعة ⏳
                    </span>
                    @endif
                </div>

                <p style="font-size: 13.5px; color: var(--text-main); margin-bottom: 8px; line-height: 1.6;">
                    {{ $r->description }}
                </p>

                @if($r->admin_notes)
                <div style="background: rgba(16, 185, 129, 0.08); border-right: 3px solid var(--success); padding: 8px 12px; border-radius: 4px; font-size: 12.5px; color: var(--success); margin-bottom: 6px;">
                    <b>رد الفريق الأكاديمي:</b> {{ $r->admin_notes }}
                </div>
                @endif

                <div style="font-size: 11.5px; color: var(--text-muted);">
                    تاريخ التقديم: {{ $r->created_at->format('Y-m-d H:i') }} ({{ $r->created_at->diffForHumans() }})
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 40px; margin-bottom: 8px;">💬</div>
                <div>لم تقم بتقديم أي بلاغات سابقة.</div>
                <div style="font-size: 12.5px; margin-top: 4px;">إذا واجهت أي سؤال غير واضح أو مشكلة، يمكنك الإبلاغ عنها وسنساعدك فوراً.</div>
            </div>
            @endforelse
        </div>

        <div style="margin-top: 18px;">
            {{ $reports->links() }}
        </div>
    </div>

</div>
@endsection
