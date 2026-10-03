@extends('layouts.app')

@section('title', 'تقديم بلاغ أو ملاحظة — Report Issue')

@section('content')
<div style="max-width: 700px; margin: auto;">
    <div class="card" style="padding: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
            <div style="font-size: 32px; background: rgba(239, 68, 68, 0.12); width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                🚨
            </div>
            <div>
                <h1 style="font-size: 22px; font-weight: 900;">تقديم بلاغ أو الإبلاغ عن مشكلة</h1>
                <p style="color: var(--text-muted); font-size: 13.5px;">سؤال خاطئ، مشكلة في اختبار أو فيديو، أو محتوى غير واضح</p>
            </div>
        </div>

        <form action="{{ route('feedback.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">نوع البلاغ أو الملاحظة *</label>
                <select name="type" class="form-control" required>
                    <option value="QUESTION">سؤال خاطئ أو إجابة غير دقيقة (Question Issue)</option>
                    <option value="QUIZ">مشكلة أثناء حل الاختبار (Quiz Issue)</option>
                    <option value="LESSON">ملاحظة على فيديو أو شرح درس (Lesson Issue)</option>
                    <option value="COURSE">مشكلة في محتوى الدورة (Course Issue)</option>
                    <option value="GENERAL">اقتراح عام أو استفسار للمنصة (General Feedback)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">عنوان البلاغ الموجز *</label>
                <input type="text" name="title" class="form-control" placeholder="مثال: خطأ في صياغة السؤال الثاني باختبار الفيزياء" required>
            </div>

            <div class="form-group">
                <label class="form-label">تفاصيل المشكلة والملاحظة بدقة *</label>
                <textarea name="description" class="form-control" rows="4" placeholder="اشرح بالتفصيل ما واجهته أو الخيار الذي تعتقد بصحته..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="{{ route('feedback.index') }}" class="btn">إلغاء</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    🚀 إرسال البلاغ للفريق الأكاديمي
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
