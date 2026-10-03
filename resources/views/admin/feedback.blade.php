@extends('layouts.app')

@section('title', 'إدارة البلاغات والشكاوى — Feedback & Support Queue')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>🚨</span> إدارة البلاغات وملاحظات الأسئلة والمحتوى
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">متابعة بلاغات الطلاب والمعلمين حول أي أخطاء في الأسئلة أو مشاكل تقنية وحلها فورياً</p>
        </div>

        <a href="{{ route('admin.index') }}" class="btn">
            ⬅ العودة للوحة الإدارة
        </a>
    </div>

    <div class="card">
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($reports as $r)
            <div style="padding: 18px; background: var(--surface); border: 1px solid {{ $r->status === 'PENDING' ? 'rgba(239, 68, 68, 0.3)' : 'var(--surface-border)' }}; border-radius: var(--radius-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="role-pill" style="@if($r->type === 'QUESTION') background: rgba(239, 68, 68, 0.15); color: #f87171; @else background: rgba(6, 182, 212, 0.15); color: #22d3ee; @endif">
                            {{ $r->type }}
                        </span>
                        <h4 style="font-size: 15px; font-weight: 800;">{{ $r->title }}</h4>
                    </div>

                    <div>
                        @if($r->status === 'PENDING')
                        <span class="role-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                            قيد المراجعة ⏳
                        </span>
                        @else
                        <span class="role-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                            تم الحل والمعالجة ✓
                        </span>
                        @endif
                    </div>
                </div>

                <p style="font-size: 13.5px; color: var(--text-main); margin-bottom: 12px; line-height: 1.6;">
                    {{ $r->description }}
                </p>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 12px; color: var(--text-muted); padding-top: 10px; border-top: 1px solid var(--surface-border);">
                    <div>
                        مقدم البلاغ: <b>{{ $r->user->name }}</b> &bull; {{ $r->created_at->diffForHumans() }}
                    </div>

                    @if($r->status === 'PENDING')
                    <form action="{{ route('admin.feedback.resolve', $r->id) }}" method="POST" style="display: flex; gap: 8px; align-items: center;">
                        @csrf
                        <input type="text" name="admin_notes" placeholder="ملاحظة أو رد المشرف للطالب..." class="form-control" style="font-size: 12px; padding: 4px 10px; width: 220px;" required>
                        <button type="submit" class="btn btn-sm btn-success">
                            حل البلاغ وإشعار الطالب ✓
                        </button>
                    </form>
                    @else
                    <div style="color: var(--success); font-weight: 700;">
                        رد الإدارة: {{ $r->admin_notes }}
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 38px; margin-bottom: 8px;">✅</div>
                <div>لا توجد بلاغات معلقة حالياً، المنصة تعمل بأعلى استقرار!</div>
            </div>
            @endforelse
        </div>

        <div style="margin-top: 18px;">
            {{ $reports->links() }}
        </div>
    </div>

</div>
@endsection
