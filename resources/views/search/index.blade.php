@extends('layouts.app')

@section('title', 'البحث العام الموحد — Global Search')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Search Header & Input -->
    <div>
        <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">
            <span>🔍</span> محرك البحث العام الموحد (Global Search)
        </h1>
        <p style="color: var(--text-muted); font-size: 14px;">ابحث في كافة أرجاء المنصة: الكورسات، الاختبارات الذكية، الدروس التعليمية، ونخبة المعلمين</p>
    </div>

    <!-- Search Bar Card -->
    <div class="card" style="padding: 20px;">
        <form action="{{ route('search') }}" method="GET">
            <div style="display: flex; gap: 10px;">
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       placeholder="اكتب كلمة البحث هنا (مثال: فيزياء، برمجة، نيوتن، CS...)"
                       class="form-control"
                       style="font-size: 15px; padding: 12px 18px;"
                       required>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; white-space: nowrap;">
                    بحث 🚀
                </button>
            </div>

            <!-- Tab Filters -->
            <div style="display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap;">
                <a href="{{ route('search', ['q' => $q, 'tab' => 'all']) }}" class="btn btn-sm {{ $tab === 'all' ? 'btn-primary' : '' }}">
                    الكل ({{ $totalResults }})
                </a>
                <a href="{{ route('search', ['q' => $q, 'tab' => 'courses']) }}" class="btn btn-sm {{ $tab === 'courses' ? 'btn-primary' : '' }}">
                    الكورسات ({{ $courses->count() }})
                </a>
                <a href="{{ route('search', ['q' => $q, 'tab' => 'quizzes']) }}" class="btn btn-sm {{ $tab === 'quizzes' ? 'btn-primary' : '' }}">
                    الاختبارات ({{ $quizzes->count() }})
                </a>
                <a href="{{ route('search', ['q' => $q, 'tab' => 'lessons']) }}" class="btn btn-sm {{ $tab === 'lessons' ? 'btn-primary' : '' }}">
                    الدروس ({{ $lessons->count() }})
                </a>
                <a href="{{ route('search', ['q' => $q, 'tab' => 'teachers']) }}" class="btn btn-sm {{ $tab === 'teachers' ? 'btn-primary' : '' }}">
                    المعلمين ({{ $teachers->count() }})
                </a>
            </div>
        </form>
    </div>

    <!-- Results Container -->
    @if(!empty($q))
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Courses Section -->
        @if($courses->count() > 0 && ($tab === 'all' || $tab === 'courses'))
        <div class="card">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>📚</span> الكورسات المطابقة ({{ $courses->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
                @foreach($courses as $c)
                <div style="padding: 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span class="role-pill">{{ $c->subject }}</span>
                        <span class="role-pill">{{ $c->difficulty }}</span>
                    </div>
                    <h4 style="font-size: 14.5px; font-weight: 800; margin-bottom: 6px;">
                        <a href="{{ route('courses.show', $c->id) }}" style="color: inherit;">{{ $c->title }}</a>
                    </h4>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">المعلم: {{ $c->teacher->name }}</div>
                    <a href="{{ route('courses.show', $c->id) }}" class="btn btn-sm btn-primary" style="width: 100%;">استعراض الدورة</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quizzes Section -->
        @if($quizzes->count() > 0 && ($tab === 'all' || $tab === 'quizzes'))
        <div class="card">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>📝</span> الاختبارات المطابقة ({{ $quizzes->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
                @foreach($quizzes as $quiz)
                <div style="padding: 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span class="role-pill">الكود: {{ $quiz->code }}</span>
                        <span class="role-pill">{{ $quiz->duration_minutes }} دقيقة</span>
                    </div>
                    <h4 style="font-size: 14.5px; font-weight: 800; margin-bottom: 6px;">{{ $quiz->title }}</h4>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">المعلم: {{ $quiz->teacher->name }}</div>
                    <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-sm btn-accent" style="width: 100%;">بدء الاختبار ⚡</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Lessons Section -->
        @if($lessons->count() > 0 && ($tab === 'all' || $tab === 'lessons'))
        <div class="card">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>🎬</span> الدروس التعليمية المطابقة ({{ $lessons->count() }})
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($lessons as $l)
                <div style="padding: 12px 16px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 800; font-size: 14px;">{{ $l->title }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">ضمن دورة: {{ $l->course->title }}</div>
                    </div>
                    <a href="{{ route('courses.lesson', [$l->course_id, $l->id]) }}" class="btn btn-sm btn-primary">مشاهدة الدرس</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Teachers Section -->
        @if($teachers->count() > 0 && ($tab === 'all' || $tab === 'teachers'))
        <div class="card">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>👨‍🏫</span> المعلمين المطابقين ({{ $teachers->count() }})
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
                @foreach($teachers as $t)
                <div style="padding: 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; align-items: center; gap: 12px;">
                    <img src="{{ $t->avatar_url }}" style="width: 44px; height: 44px; border-radius: 50%;">
                    <div>
                        <div style="font-weight: 800; font-size: 14px;">{{ $t->name }}</div>
                        <div style="font-size: 11.5px; color: var(--text-muted); line-height: 1.4;">{{ Str::limit($t->bio, 40) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($totalResults === 0)
        <div class="card" style="text-align: center; padding: 50px; color: var(--text-muted);">
            <div style="font-size: 44px; margin-bottom: 8px;">🔍</div>
            <h3>لم نعثر على أي نتائج تطابق "{{ $q }}"</h3>
            <p style="font-size: 13.5px; margin-top: 4px;">تأكد من كتابة الكلمات بشكل صحيح، أو جرب البحث بكلمة عامة أخرى.</p>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
