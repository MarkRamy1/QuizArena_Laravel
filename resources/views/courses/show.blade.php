@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div style="display: flex; flex-direction: column; gap: 26px;">

    <!-- Course Header Card -->
    <div class="card" style="padding: 30px; position: relative; overflow: hidden; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.05)), var(--bg-elevated);">
        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
            <img src="{{ $course->thumbnail ?? 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80' }}"
                 alt="{{ $course->title }}"
                 style="width: 200px; height: 140px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--surface-border); flex-shrink: 0;">

            <div style="flex: 1; min-width: 280px;">
                <div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                    <span class="role-pill">{{ $course->subject }}</span>
                    <span class="role-pill">{{ $course->difficulty }}</span>
                    @if($course->grade)
                    <span class="role-pill">{{ $course->grade }}</span>
                    @endif
                </div>

                <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 8px;">{{ $course->title }}</h1>
                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
                    {{ $course->description }}
                </p>

                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="{{ $course->teacher->avatar_url }}" style="width: 32px; height: 32px; border-radius: 50%;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px;">{{ $course->teacher->name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">مؤلف ومدرس الدورة</div>
                        </div>
                    </div>

                    <!-- Progress Badge -->
                    <div style="background: var(--surface); padding: 8px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); text-align: center;">
                        <div style="font-size: 11px; color: var(--text-muted);">نسبة إتمام الدورة</div>
                        <div style="font-size: 18px; font-weight: 900; color: {{ $progress >= 100 ? 'var(--success)' : 'var(--primary-light)' }};">
                            {{ $progress }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar Line -->
        <div style="margin-top: 24px;">
            <div style="height: 8px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
                <div style="height: 100%; width: {{ $progress }}%; background: linear-gradient(90deg, var(--primary), var(--accent)); transition: width 0.4s;"></div>
            </div>
            @if($progress >= 100)
            <div style="margin-top: 10px; font-size: 13px; font-weight: 800; color: var(--success); display: flex; align-items: center; gap: 6px;">
                <span>🎓</span> تهانينا! لقد أتممت جميع دروس هذه الدورة، وتم إصدار شهادة إتمام معتمدة لك في قسم الشهادات.
            </div>
            @endif
        </div>
    </div>

    <!-- Course Content Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Lessons Syllabus List -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> المنهج والدروس التعليمية ({{ $course->lessons->count() }} درساً)
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse($course->lessons as $lesson)
                    @php
                        $isCompleted = $lesson->isCompletedBy(Auth::user());
                    @endphp
                    <a href="{{ route('courses.lesson', [$course->id, $lesson->id]) }}"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: var(--surface); border: 1px solid {{ $isCompleted ? 'rgba(16, 185, 129, 0.3)' : 'var(--surface-border)' }}; border-radius: var(--radius-md); transition: all 0.2s;"
                       onmouseover="this.style.borderColor='var(--primary)'"
                       onmouseout="this.style.borderColor='{{ $isCompleted ? 'rgba(16, 185, 129, 0.3)' : 'var(--surface-border)' }}'">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-size: 20px;">
                                @if($isCompleted) ✅ @else 🎬 @endif
                            </span>
                            <div>
                                <div style="font-weight: 800; font-size: 14px; color: var(--text-main);">
                                    {{ $lesson->title }}
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    ⏱️ {{ $lesson->duration_minutes }} دقيقة &bull; فيديو وشرح تفاعلي
                                </div>
                            </div>
                        </div>

                        <span class="btn btn-sm {{ $isCompleted ? 'btn-success' : 'btn-primary' }}" style="padding: 6px 14px;">
                            @if($isCompleted) تمت المشاهدة ✓ @else بدء الدرس ◀ @endif
                        </span>
                    </a>
                    @empty
                    <p style="text-align: center; color: var(--text-muted); padding: 30px;">
                        لم تتم إضافة دروس لهذه الدورة بعد.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Teacher: Add Lesson Form -->
            @if(Auth::id() === $course->teacher_id || Auth::user()->isAdmin())
            <div class="card" style="border: 1px dashed var(--primary);">
                <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>➕</span> إضافة درس تعليمي جديد لهذا الكورس
                </h3>

                <form action="{{ route('courses.lessons.store', $course->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">عنوان الدرس</label>
                        <input type="text" name="title" class="form-control" required placeholder="مثال: الدرس الرابع: التعامل مع قواعد البيانات">
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">رابط الفيديو (YouTube Embed أو MP4)</label>
                            <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/embed/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">المدة المقدرة (بالدقائق)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="20" min="1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">وصف ونقاط الدرس الأساسية</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="ملاحظات وشروحات موجزة لمحتوى الدرس..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        💾 حفظ وإضافة الدرس
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Sidebar Details: Quizzes & Certificate -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Quizzes in this Course -->
            <div class="card">
                <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> اختبارات الدورة والتقييم
                </h3>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse($course->quizzes as $quiz)
                    <div style="padding: 12px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                        <div style="font-weight: 800; font-size: 13.5px; margin-bottom: 4px;">{{ $quiz->title }}</div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 8px;">
                            الكود: <b>{{ $quiz->code }}</b> &bull; {{ $quiz->duration_minutes }} دقيقة
                        </div>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-sm btn-accent" style="width: 100%;">
                            دخول الاختبار ⚡
                        </a>
                    </div>
                    @empty
                    <p style="font-size: 12.5px; color: var(--text-muted); text-align: center; padding: 10px;">
                        لا توجد اختبارات مرفقة حالياً.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Certificate Box -->
            <div class="card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), transparent), var(--bg-elevated); border-color: rgba(245, 158, 11, 0.3);">
                <div style="font-size: 32px; margin-bottom: 8px;">📜</div>
                <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 6px;">شهادة الإتمام المعتمدة</h4>
                <p style="font-size: 12.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 14px;">
                    احرص على إنهاء كافة الدروس بنسبة 100% لتحصل تلقائياً على شهادة إنجاز رسمية موثقة برمز QR وكود تحقق فريد.
                </p>
                <a href="{{ route('certificates.index') }}" class="btn btn-sm" style="width: 100%;">
                    استعراض شهاداتي
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
