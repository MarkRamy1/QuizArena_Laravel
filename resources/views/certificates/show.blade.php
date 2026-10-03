@extends('layouts.app')

@section('title', 'شهادة إنجاز معتمدة — ' . $certificate->student->name)

@section('styles')
<style>
    @page {
        size: A4 landscape;
        margin: 8mm;
    }
    @media print {
        header, aside, footer, .app-header, .sidebar-area, .app-footer, .no-print {
            display: none !important;
        }
        body {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .main-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            display: block !important;
        }
        .content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }
        .printable-certificate {
            box-shadow: none !important;
            border: 10px double #c59b35 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            page-break-inside: avoid !important;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 950px; margin: auto;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('certificates.index') }}" class="btn btn-sm">
            &rarr; العودة لسجل الشهادات
        </a>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-primary" onclick="window.print()" style="background: #c59b35; border-color: #b38928;">
                طباعة وتحميل الشهادة PDF 🖨️
            </button>
            <a href="{{ route('certificates.verify', $certificate->verification_code) }}" target="_blank" class="btn btn-sm">
                صفحة التحقق العلنية 🔍
            </a>
        </div>
    </div>

    <!-- The Prestigious Printable Certificate -->
    <div class="printable-certificate" style="background: linear-gradient(135deg, #ffffff, #fcf9ea, #f5f0d3); color: #1c2435; padding: 50px 40px; border: 12px double #c59b35; border-radius: 12px; text-align: center; box-shadow: 0 25px 70px rgba(0,0,0,0.5); position: relative; overflow: hidden;">
        <!-- Watermark Background -->
        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0.04; font-size: 160px; pointer-events: none; font-weight: 900;">
            QUIZARENA
        </div>

        <!-- Official Header -->
        <div style="font-size: 50px; margin-bottom: 8px;">🏛️</div>
        <div style="font-family: 'Cairo', Georgia, serif; font-size: 15px; font-weight: 800; letter-spacing: 2px; color: #8b6515; text-transform: uppercase;">
            QuizArena ULTRA — المنصة التعليمية الرقمية المعتمدة
        </div>
        <h1 style="color: #78530c; font-size: 38px; font-weight: 900; margin: 12px 0 20px;">
            شهادة إنجاز وتفوق أكاديمي
        </h1>

        <div style="font-size: 16px; color: #555; margin-bottom: 12px;">
            تشهد إدارة المنصة والمعلم المشرف بأن الطالب / الطالبة:
        </div>

        <!-- Student Name -->
        <div style="font-size: 34px; font-weight: 900; color: #111827; border-bottom: 2px solid #c59b35; display: inline-block; padding: 0 40px 8px; margin-bottom: 18px;">
            {{ $certificate->student->name }}
        </div>

        <div style="font-size: 16px; color: #444; max-width: 650px; margin: auto; line-height: 1.8; margin-bottom: 28px;">
            قد اجتاز(ت) بنجاح واقتدار كافة متطلبات الاختبار التقييمي المعياري بعنوان:
            <br>
            <b style="font-size: 20px; color: #78530c;">{{ $certificate->quiz->title }}</b>
            <br>
            بمعدل إنجاز متميز قدره: <b style="font-size: 22px; color: #15803d;">{{ $certificate->percentage }}%</b>
        </div>

        <!-- Official Footer / Signatures -->
        <div style="display: flex; justify-content: space-between; align-items: end; margin-top: 40px; padding: 0 30px; border-top: 1px solid #dcd1a6; padding-top: 24px;">
            <div style="text-align: right;">
                <div style="font-size: 13px; color: #666;">المعلم المشرف:</div>
                <div style="font-size: 17px; font-weight: 900; color: #111;">{{ $certificate->teacher->name }}</div>
                <div style="font-size: 12px; color: #888;">منصة QuizArena التعليمية</div>
            </div>

            <!-- Golden Embossed Seal Badge -->
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 75px; height: 75px; border-radius: 50%; border: 3px solid #c59b35; background: radial-gradient(circle, #fff, #edd98e); display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 4px 15px rgba(197, 155, 53, 0.4);">
                    ★
                </div>
                <div style="font-size: 11px; font-weight: 900; color: #78530c; margin-top: 4px;">معتمد رقمياً</div>
            </div>

            <div style="text-align: left;">
                <div style="font-size: 13px; color: #666;">تاريخ الإصدار:</div>
                <div style="font-size: 16px; font-weight: 800; color: #111;">{{ $certificate->issued_at?->format('Y-m-d') }}</div>
                <div style="font-size: 11px; color: #888; font-family: monospace; margin-top: 2px;">
                    {{ $certificate->certificate_number }}
                </div>
            </div>
        </div>

        <!-- Verification Footnote -->
        <div style="margin-top: 28px; padding-top: 16px; border-top: 1px dashed #dcd1a6; font-size: 12px; color: #777;">
            كود التحقق الرقمي الفريد: <b style="font-family: monospace; font-size: 13px; color: #78530c;">{{ $certificate->verification_code }}</b>
            &bull; يمكن التحقق من صحة هذه الشهادة عبر الرابط:
            <span style="font-family: monospace; color: #1d4ed8;">{{ route('certificates.verify', $certificate->verification_code) }}</span>
        </div>
    </div>
</div>
@endsection
