@extends('layouts.app')

@section('title', 'إنشاء دورة تعليمية جديدة — Create Course')

@section('content')
<div style="max-width: 800px; margin: auto;">
    <div class="card" style="padding: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
            <div style="font-size: 32px; background: rgba(79, 70, 229, 0.12); width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📚
            </div>
            <div>
                <h1 style="font-size: 22px; font-weight: 900;">إنشاء دورة تعليمية جديدة</h1>
                <p style="color: var(--text-muted); font-size: 13.5px;">أضف مساراً تعليمياً متكاملاً يتضمن دروساً فيديو واختبارات قياس مستوى</p>
            </div>
        </div>

        <form action="{{ route('courses.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">عنوان الدورة التعليمية *</label>
                <input type="text" name="title" class="form-control" required placeholder="مثال: هندسة تطبيقات الويب مع Laravel و Vue.js" value="{{ old('title') }}">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">المادة الدراسية / التخصص *</label>
                    <input type="text" name="subject" class="form-control" required placeholder="مثال: علوم الحاسب، الفيزياء، الرياضيات" value="{{ old('subject') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">المستوى / المرحلة الدراسية</label>
                    <input type="text" name="grade" class="form-control" placeholder="مثال: المرحلة الثانوية، المستوى الجامعي" value="{{ old('grade') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">مستوى الصعوبة *</label>
                    <select name="difficulty" class="form-control" required>
                        <option value="EASY">سهل (Easy)</option>
                        <option value="MEDIUM" selected>متوسط (Medium)</option>
                        <option value="HARD">متقدم (Hard)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">ربط بفصل دراسي (اختياري)</label>
                    <select name="classroom_id" class="form-control">
                        <option value="">بدون فصل (عام لكافة الطلاب)</option>
                        @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->name }} ({{ $cls->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">رابط صورة الغلاف (Thumbnail URL)</label>
                <input type="url" name="thumbnail" class="form-control" placeholder="https://images.unsplash.com/..." value="{{ old('thumbnail', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80') }}">
            </div>

            <div class="form-group">
                <label class="form-label">وصف شامل للدورة وأهدافها التعليمية *</label>
                <textarea name="description" class="form-control" rows="4" required placeholder="اكتب نبذة وافية عما سيتعلمه الطالب في هذا المسار...">{{ old('description') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="{{ route('courses.index') }}" class="btn">إلغاء</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    🚀 نشر الكورس والبدء بإضافة الدروس
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
