@extends('layouts.app')

@section('title', 'الكورسات والمسارات التعليمية — Courses')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header & Filters -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>📚</span> الكورسات والمسارات التعليمية المتكاملة
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">تصفح مسارات التعلم المصممة بأعلى جودة لمساعدتك على التفوق الأكاديمي والمهني</p>
        </div>

        @if(Auth::user()->isTeacher() || Auth::user()->isAdmin())
        <a href="{{ route('courses.create') }}" class="btn btn-primary">
            ➕ إنشاء كورس جديد
        </a>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 16px 20px;">
        <form action="{{ route('courses.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="ابحث باسم الكورس أو الموضوع..."
                   class="form-control"
                   style="max-width: 280px; font-size: 13.5px;">

            <select name="subject" class="form-control" style="max-width: 180px; font-size: 13.5px;">
                <option value="">جميع المواد الدراسية</option>
                @foreach($subjects as $sub)
                <option value="{{ $sub }}" {{ request('subject') === $sub ? 'selected' : '' }}>{{ $sub }}</option>
                @endforeach
            </select>

            <select name="difficulty" class="form-control" style="max-width: 150px; font-size: 13.5px;">
                <option value="">كافة الصعوبات</option>
                <option value="EASY" {{ request('difficulty') === 'EASY' ? 'selected' : '' }}>سهل (Easy)</option>
                <option value="MEDIUM" {{ request('difficulty') === 'MEDIUM' ? 'selected' : '' }}>متوسط (Medium)</option>
                <option value="HARD" {{ request('difficulty') === 'HARD' ? 'selected' : '' }}>متقدم (Hard)</option>
            </select>

            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                تصفية 🔍
            </button>
            <a href="{{ route('courses.index') }}" class="btn" style="padding: 10px 16px;">
                إعادة تعيين
            </a>
        </form>
    </div>

    <!-- Courses Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 22px;">
        @forelse($courses as $course)
        @php
            $userProgress = $course->getProgressForUser(Auth::user());
        @endphp
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <div style="position: relative; height: 180px; overflow: hidden;">
                <img src="{{ $course->thumbnail ?? 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80' }}"
                     alt="{{ $course->title }}"
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.05)'"
                     onmouseout="this.style.transform='scale(1)'">
                <span class="role-pill" style="position: absolute; top: 12px; right: 12px; background: rgba(15,23,42,0.85); backdrop-filter: blur(8px);">
                    {{ $course->subject }}
                </span>
                <span class="role-pill" style="position: absolute; top: 12px; left: 12px; background: rgba(15,23,42,0.85); backdrop-filter: blur(8px); @if($course->difficulty === 'HARD') color: #f87171; @elseif($course->difficulty === 'MEDIUM') color: #fbbf24; @else color: #34d399; @endif">
                    {{ $course->difficulty }}
                </span>
            </div>

            <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; line-height: 1.4;">
                    <a href="{{ route('courses.show', $course->id) }}" style="color: inherit;">
                        {{ $course->title }}
                    </a>
                </h3>

                <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px; flex: 1;">
                    {{ Str::limit($course->description, 110) }}
                </p>

                <!-- Instructor & Lessons info -->
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12.5px; color: var(--text-muted); padding-top: 12px; border-top: 1px solid var(--surface-border); margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ $course->teacher->avatar_url }}" style="width: 24px; height: 24px; border-radius: 50%;">
                        <span>{{ $course->teacher->name }}</span>
                    </div>
                    <span>{{ $course->lessons->count() }} دروس</span>
                </div>

                <!-- Progress Bar -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">
                        <span>نسبة الإنجاز</span>
                        <span>{{ $userProgress }}%</span>
                    </div>
                    <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $userProgress }}%; background: linear-gradient(90deg, var(--primary), var(--accent));"></div>
                    </div>
                </div>

                <a href="{{ route('courses.show', $course->id) }}" class="btn btn-primary" style="width: 100%;">
                    @if($userProgress > 0)
                        استكمال الدورة ({{ $userProgress }}%)
                    @else
                        بدء التعلم 🚀
                    @endif
                </a>
            </div>
        </div>
        @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--text-muted);">
            <div style="font-size: 48px; margin-bottom: 12px;">🔍</div>
            <h3>لا توجد كورسات مطابقة لبحثك حالياً</h3>
            <p>جرب تغيير شروط البحث أو التصفية.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div>
        {{ $courses->links() }}
    </div>

</div>
@endsection
