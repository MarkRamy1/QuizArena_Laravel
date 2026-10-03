@extends('layouts.app')

@section('title', $lesson->title . ' — ' . $course->title)

@section('content')
<div style="display: flex; flex-direction: column; gap: 22px;">

    <!-- Top Breadcrumb & Progress Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">
                <a href="{{ route('courses.index') }}" style="text-decoration: underline;">الكورسات</a> &bull;
                <a href="{{ route('courses.show', $course->id) }}" style="text-decoration: underline;">{{ $course->title }}</a>
            </div>
            <h1 style="font-size: 22px; font-weight: 900;">{{ $lesson->title }}</h1>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('ai.tutor') }}" class="btn btn-sm btn-accent" title="اسأل المساعد الذكي عن هذا الدرس">
                🤖 اسأل المعلم الذكي عن الدرس
            </a>
            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-sm">
                📋 قائمة الدروس
            </a>
        </div>
    </div>

    <!-- Main Lesson Content Area: Player & Playlist -->
    <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px;">
        <!-- Left: Video & Details -->
        <div style="display: flex; flex-direction: column; gap: 18px;">
            <!-- Video Player Box -->
            <div class="card" style="padding: 0; overflow: hidden; background: #000; border-radius: var(--radius-lg);">
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                    @if($lesson->video_url)
                    <iframe src="{{ $lesson->video_url }}"
                            title="{{ $lesson->title }}"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    @else
                    <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); gap: 12px; background: #0f172a;">
                        <span style="font-size: 48px;">🎬</span>
                        <div style="font-size: 15px; font-weight: 700;">فيديو الدرس قيد المونتاج والرفع</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Lesson Actions & Status Strip -->
            <div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; padding: 18px 24px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">{{ $isCompleted ? '✅' : '⏳' }}</span>
                    <div>
                        <div style="font-weight: 800; font-size: 14px;">
                            {{ $isCompleted ? 'تم إكمال هذا الدرس بنجاح ✓' : 'الدرس قيد المشاهدة والدراسة' }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            المدة: {{ $lesson->duration_minutes }} دقيقة &bull; إكمال الدرس يمنحك +30 XP
                        </div>
                    </div>
                </div>

                <form action="{{ route('courses.lessons.complete', $lesson->id) }}" method="POST">
                    @csrf
                    @if(!$isCompleted)
                    <button type="submit" class="btn btn-primary" style="padding: 10px 22px;">
                        ✓ تم مشاهدة الدرس (+30 XP)
                    </button>
                    @else
                    <button type="submit" class="btn btn-success" style="padding: 10px 22px;">
                        ✓ مكتمل (إعادة تأكيد)
                    </button>
                    @endif
                </form>
            </div>

            <!-- Lesson Description & Key Concepts -->
            <div class="card">
                <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> ملخص ومحتوى الدرس
                </h3>
                <div style="font-size: 14.5px; line-height: 1.8; color: var(--text-main);">
                    {!! nl2br(e($lesson->description ?? 'يقدم هذا الدرس شرحاً مفصلاً لكافة المفاهيم النظرية والتطبيقية، مع أمثلة برمجية ورسوم توضيحية تساعد الطالب على استيعاب المادة بسهولة.')) !!}
                </div>
            </div>

            <!-- Next & Previous Lesson Navigation -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                @if($prevLesson)
                <a href="{{ route('courses.lesson', [$course->id, $prevLesson->id]) }}" class="btn">
                    ⬅ الدرس السابق: {{ Str::limit($prevLesson->title, 25) }}
                </a>
                @else
                <div></div>
                @endif

                @if($nextLesson)
                <a href="{{ route('courses.lesson', [$course->id, $nextLesson->id]) }}" class="btn btn-primary">
                    الدرس التالي ➡: {{ Str::limit($nextLesson->title, 25) }}
                </a>
                @else
                <a href="{{ route('courses.show', $course->id) }}" class="btn btn-success">
                    🎓 تهانينا! نهاية المنهج
                </a>
                @endif
            </div>
        </div>

        <!-- Right: Playlist Curriculum Sidebar -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="card" style="padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h3 style="font-size: 16px; font-weight: 800;">منهج الدورة</h3>
                    <span class="role-pill">{{ $progress }}% مكتمل</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; max-height: 540px; overflow-y: auto;">
                    @foreach($lessons as $index => $item)
                    @php $itemDone = $item->isCompletedBy(Auth::user()); @endphp
                    <a href="{{ route('courses.lesson', [$course->id, $item->id]) }}"
                       style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; text-decoration: none;
                              {{ $item->id === $lesson->id ? 'background: rgba(79, 70, 229, 0.15); border: 1px solid var(--primary); color: #fff;' : 'background: var(--surface); border: 1px solid var(--surface-border); color: var(--text-muted);' }}">
                        <span>{{ $itemDone ? '✅' : ($item->id === $lesson->id ? '▶️' : ($index + 1)) }}</span>
                        <div style="flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $item->title }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
