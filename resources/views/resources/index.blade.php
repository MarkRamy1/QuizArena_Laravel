@extends('layouts.app')

@section('title', 'مكتبة المصادر والدروس التعليمية')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 900;">📚 مكتبة المصادر والدروس التعليمية</h1>
        <p style="color: var(--muted); font-size: 14px;">كتب، شروحات مرئية، وملخصات تدريبية لمساعدتك على التفوق الأكاديمي</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 24px; padding: 18px;">
    <form action="{{ route('resources.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث بعنوان المصدر أو الكلمات المفتاحية...">
        </div>

        <div style="flex: 1; min-width: 170px;">
            <select name="type" onchange="this.form.submit()">
                <option value="">جميع أنواع المصادر</option>
                <option value="BOOK" {{ request('type') == 'BOOK' ? 'selected' : '' }}>📖 كتب ومذكرات</option>
                <option value="VIDEO" {{ request('type') == 'VIDEO' ? 'selected' : '' }}>🎥 دروس مرئية</option>
                <option value="FILE" {{ request('type') == 'FILE' ? 'selected' : '' }}>📄 ملفات ووثائق</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 170px;">
            <select name="subject_id" onchange="this.form.submit()">
                <option value="">جميع المواد الدراسية</option>
                @foreach($subjects as $subj)
                <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                    {{ $subj->name_ar }}
                </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn primary" style="padding: 11px 20px;">
            تصفية 🔍
        </button>

        @if(request()->hasAny(['search', 'type', 'subject_id']))
        <a href="{{ route('resources.index') }}" class="btn" style="padding: 11px 16px;">
            إلغاء ✕
        </a>
        @endif
    </form>
</div>

<!-- Resources Grid -->
<div class="grid" style="margin-bottom: 30px;">
    @forelse($resources as $res)
    @php
        $typeIcons = ['BOOK' => '📖 كتاب', 'VIDEO' => '🎥 فيديو', 'FILE' => '📄 ملف', 'LINK' => '🔗 رابط'];
    @endphp
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="pill-stat" style="font-size: 11px;">
                    {{ $typeIcons[$res->type] ?? '📄 ملف' }}
                </span>
                <span style="font-size: 12px; color: var(--secondary); font-weight: 700;">
                    {{ $res->subject->name_ar ?? 'عام' }}
                </span>
            </div>

            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">
                {{ $res->title }}
            </h3>

            <p style="color: var(--muted); font-size: 13px; line-height: 1.6; margin-bottom: 16px;">
                {{ Str::limit($res->description, 110) }}
            </p>

            <div style="font-size: 12px; color: var(--muted); margin-bottom: 14px;">
                <div>المرحلة: <b style="color: var(--text);">{{ $res->grade ?? 'جميع المراحل' }}</b></div>
                <div>إشراف المعلم: <b style="color: var(--text);">{{ $res->teacher->name }}</b></div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--line); padding-top: 14px;">
            <a href="{{ $res->external_url ?? '#' }}" target="_blank" class="btn primary" style="width: 100%;">
                فتح المصدر والاطلاع عليه ↗
            </a>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: var(--muted);" class="card">
        <div style="font-size: 40px; margin-bottom: 10px;">📚</div>
        <h3>لم يتم العثور على مصادر تطابق اختياراتك.</h3>
    </div>
    @endforelse
</div>

{{ $resources->links() }}
@endsection
