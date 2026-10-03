@extends('layouts.app')

@section('title', 'مركز الإشعارات والتنبيهات — Notifications')

@section('content')
<div style="max-width: 850px; margin: auto; display: flex; flex-direction: column; gap: 22px;">

    <!-- Notifications Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>🔔</span> مركز الإشعارات والتنبيهات
            </h1>
            <p style="color: var(--text-muted); font-size: 13.5px;">تحديثات الاختبارات، إضافة الكورسات الجديدة، الأوسمة، والشهادات المكتسبة</p>
        </div>

        <form action="{{ route('notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm">
                ✓ تحديد الكل كمقروء
            </button>
        </form>
    </div>

    <!-- Notifications List Card -->
    <div class="card">
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($notifications as $n)
            <div style="padding: 16px; background: var(--surface); border: 1px solid {{ !$n->is_read ? 'rgba(79, 70, 229, 0.3)' : 'var(--surface-border)' }}; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between; gap: 14px; {{ !$n->is_read ? 'background: rgba(79, 70, 229, 0.05);' : '' }}">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="font-size: 24px; flex-shrink: 0; margin-top: 2px;">
                        @if($n->type === 'QUIZ') 📝
                        @elseif($n->type === 'ACHIEVEMENT') 🏆
                        @elseif($n->type === 'CERTIFICATE') 📜
                        @elseif($n->type === 'COURSE') 📚
                        @elseif($n->type === 'WARNING') ⚠️
                        @else 🔔
                        @endif
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 14.5px; margin-bottom: 2px;">
                            {{ $n->title }}
                            @if(!$n->is_read)
                            <span class="role-pill" style="font-size: 10px; margin-inline-start: 6px;">جديد</span>
                            @endif
                        </div>
                        <div style="font-size: 13px; color: var(--text-main); line-height: 1.5;">
                            {{ $n->message }}
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                            {{ $n->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                @if(!$n->is_read)
                <form action="{{ route('notifications.read', $n->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary" style="white-space: nowrap;">
                        عرض ومتابعة
                    </button>
                </form>
                @elseif($n->url)
                <a href="{{ $n->url }}" class="btn btn-sm">
                    فتح الرابط
                </a>
                @endif
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 40px; margin-bottom: 8px;">🔕</div>
                <div>لا توجد إشعارات جديدة حالياً. صندوق التنبيهات فارغ تماماً.</div>
            </div>
            @endforelse
        </div>

        <div style="margin-top: 18px;">
            {{ $notifications->links() }}
        </div>
    </div>

</div>
@endsection
