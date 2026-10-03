@extends('layouts.app')

@section('title', 'التحقق من صحة الشهادات الرقمية')

@section('content')
<div style="max-width: 800px; margin: auto; padding: 20px 0;">
    <div class="card" style="text-align: center; padding: 40px 24px; margin-bottom: 24px;">
        <div style="font-size: 48px; margin-bottom: 8px;">🛡️</div>
        <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 6px;">بوابة التحقق الرقمي من الشهادات</h1>
        <p style="color: var(--text-muted); font-size: 14px; max-width: 550px; margin: auto; margin-bottom: 24px; line-height: 1.6;">
            تتيح لك هذه البوابة التحقق من صحة وموثوقية الشهادات الصادرة عبر المنصة باستخدام كود التحقق الفريد (Verification Code)
        </p>

        <!-- Search Code Form -->
        <form action="{{ route('certificates.verify') }}" method="GET" style="max-width: 520px; margin: auto; display: flex; gap: 10px;">
            <input type="text" name="code" value="{{ $verificationCode ?? '' }}" required placeholder="أدخل كود الشهادة (مثال: VRF-XXXXXXXX)" class="form-control" style="text-transform: uppercase; font-family: monospace; font-weight: 800; font-size: 16px; text-align: center;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; white-space: nowrap;">
                تحقق 🔍
            </button>
        </form>
    </div>

    <!-- Verification Result -->
    @if($searched)
        @if($certificate)
        <div class="card" style="border: 2px solid var(--success); background: linear-gradient(135deg, rgba(16,185,129,0.1), var(--surface)); padding: 32px 28px;">
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                <div style="width: 55px; height: 55px; border-radius: 50%; background: rgba(16,185,129,0.2); display: flex; align-items: center; justify-content: center; font-size: 28px;">
                    ✅
                </div>
                <div>
                    <h2 style="font-size: 22px; font-weight: 900; color: var(--success);">شهادة أصلية وموثقة في سجلات النظام</h2>
                    <div style="font-size: 13px; color: var(--text-muted);">تم التحقق من بصمة الشهادة الرقمية بنجاح عبر خوادم المنصة الرسمية</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; font-size: 14px; margin-bottom: 24px; background: var(--bg); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--surface-border);">
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 12px;">اسم صاحب الشهادة:</span>
                    <strong style="font-size: 17px; color: var(--text-main);">{{ $certificate->student->name }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 12px;">عنوان الاختبار:</span>
                    <strong style="font-size: 17px; color: var(--accent);">{{ $certificate->quiz->title }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 12px;">نسبة النجاح المحققة:</span>
                    <strong style="font-size: 18px; color: var(--success);">{{ $certificate->percentage }}%</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 12px;">المعلم المانح:</span>
                    <strong style="font-size: 16px; color: var(--text-main);">{{ $certificate->teacher->name }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 12px;">تاريخ الإصدار الرسمي:</span>
                    <strong style="font-size: 15px; color: var(--text-main);">{{ $certificate->issued_at?->format('Y-m-d') }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 12px;">كود التحقق الفريد:</span>
                    <strong style="font-family: monospace; font-size: 15px; color: var(--accent);">{{ $certificate->verification_code }}</strong>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('certificates.show', $certificate->id) }}" class="btn btn-primary" style="background: #c59b35; border-color: #b38928;">
                    عرض النسخة الرسمية القابلة للطباعة PDF 🖨️
                </a>
            </div>
        </div>
        @else
        <div class="card" style="border: 2px solid var(--danger); background: linear-gradient(135deg, rgba(239,68,68,0.1), var(--surface)); padding: 32px 28px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
            <h2 style="font-size: 20px; font-weight: 900; color: var(--danger); margin-bottom: 8px;">لم يتم العثور على أي شهادة بهذا الكود</h2>
            <p style="font-size: 14px; color: var(--text-muted); max-width: 500px; margin: auto;">
                الكود المدخل <code>{{ $verificationCode }}</code> غير مسجل في قاعدة بيانات الشهادات المعتمدة، أو ربما تم إلغاؤه. يُرجى مراجعة الكود والمحاولة مجدداً.
            </p>
        </div>
        @endif
    @endif
</div>
@endsection
