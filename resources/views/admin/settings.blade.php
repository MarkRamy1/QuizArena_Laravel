@extends('layouts.app')

@section('title', 'إعدادات المنصة العامة — Platform Settings')

@section('content')
<div style="max-width: 800px; margin: auto; display: flex; flex-direction: column; gap: 24px;">

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 24px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>⚙️</span> إعدادات المنصة ومعايير التلعيب
            </h1>
            <p style="color: var(--text-muted); font-size: 13.5px;">تخصيص هوية المنصة، معدلات نقاط XP، وحماية النزاهة الأكاديمية</p>
        </div>

        <a href="{{ route('admin.index') }}" class="btn">⬅ العودة للوحة الإدارة</a>
    </div>

    <div class="card" style="padding: 30px;">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--surface-border);">
                🌐 الهوية الأساسية للمنصة
            </h3>

            <div class="form-group">
                <label class="form-label">اسم المنصة</label>
                <input type="text" name="platform_name" class="form-control" value="QuizArena ULTRA — المنصة التعليمية المتكاملة" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني الرسمي للدعم</label>
                    <input type="email" name="support_email" class="form-control" value="support@quizarena.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">الحد الأقصى لانتهاكات النزاهة قبل الحرمان</label>
                    <input type="number" name="max_violations" class="form-control" value="3" min="1" max="10" required>
                </div>
            </div>

            <h3 style="font-size: 16px; font-weight: 800; margin: 24px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--surface-border);">
                🎮 معايير نظام التلعيب (Gamification Rules)
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">نقاط إكمال الدرس (XP)</label>
                    <input type="number" name="xp_lesson" class="form-control" value="30" required>
                </div>
                <div class="form-group">
                    <label class="form-label">نقاط حل الاختبار الكامل (XP)</label>
                    <input type="number" name="xp_quiz" class="form-control" value="50" required>
                </div>
                <div class="form-group">
                    <label class="form-label">مكافأة العلامة الكاملة 100% (XP)</label>
                    <input type="number" name="xp_perfect" class="form-control" value="100" required>
                </div>
            </div>

            <h3 style="font-size: 16px; font-weight: 800; margin: 24px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--surface-border);">
                🤖 نموذج الذكاء الاصطناعي (AI Engine)
            </h3>

            <div class="form-group">
                <label class="form-label">محرك الذكاء الاصطناعي المفعّل</label>
                <select name="ai_engine" class="form-control">
                    <option value="gemini_flash" selected>Google DeepMind — Gemini 1.5 Flash (سرعة استجابة فائقة)</option>
                    <option value="gemini_pro">Gemini 1.5 Pro (تحليل ونمذجة عميقة)</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    💾 حفظ كافة الإعدادات
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
