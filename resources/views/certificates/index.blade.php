@extends('layouts.app')

@section('title', 'سجل الشهادات المعتمدة')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>🎓</span> سجل الشهادات الرقمية المعتمدة
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">
                جميع الشهادات الموثقة رسمياً بأكواد تحقق مشفرة وقابلة للطباعة والتحقق العلني
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('certificates.verify') }}" class="btn btn-sm btn-accent">
                🔍 التحقق من صحة شهادة
            </a>
            <a href="{{ route('quizzes.index') }}" class="btn btn-sm">
                📝 بنك الاختبارات
            </a>
        </div>
    </div>

    @if($certificates->isEmpty())
    <div class="card" style="text-align: center; padding: 48px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 12px;">📜</div>
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 6px;">لا توجد شهادات صادرة بعد</h3>
        <p style="font-size: 13.5px; max-width: 500px; margin: 0 auto 18px;">
            احصل على معدل نجاح في أحد الاختبارات لإصدار شهادة تفوق معتمدة بصيغة PDF فورياً ومشاركتها مع ولي أمرك!
        </p>
        <a href="{{ route('quizzes.index') }}" class="btn btn-primary">
            🚀 تصفح الاختبارات المتاحة
        </a>
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        @foreach($certificates as $cert)
        <div class="card" style="border: 2px solid rgba(245, 196, 81, 0.4); background: linear-gradient(135deg, rgba(245, 196, 81, 0.08), var(--surface)); display: flex; flex-direction: column; justify-content: space-between; gap: 16px;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 32px;">🏆</span>
                    <span class="role-pill" style="color: #f59e0b; border-color: rgba(245,196,81,0.4); font-size: 12px; font-weight: 800;">
                        نسبة النجاح: {{ $cert->percentage }}%
                    </span>
                </div>

                <h3 style="font-size: 18px; font-weight: 900; margin-bottom: 10px; color: var(--text-main);">
                    {{ $cert->quiz->title }}
                </h3>

                <div style="font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 6px;">
                    <div>الطالب: <strong style="color: var(--text-main);">{{ $cert->student?->name ?? 'طالب' }}</strong></div>
                    <div>المعلم المشرف: <strong style="color: var(--text-main);">{{ $cert->teacher?->name ?? 'المعلم' }}</strong></div>
                    <div>تاريخ الإصدار: <strong>{{ $cert->issued_at?->format('Y-m-d') }}</strong></div>
                    <div style="margin-top: 6px;">
                        كود التحقق: <span style="font-family: monospace; font-size: 12.5px; color: var(--accent); background: rgba(6,182,212,0.12); padding: 3px 8px; border-radius: 4px; font-weight: 700;">{{ $cert->verification_code }}</span>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--surface-border); padding-top: 14px; display: flex; gap: 10px;">
                <a href="{{ route('certificates.show', $cert->id) }}" class="btn btn-primary" style="flex: 1; background: #c59b35; border-color: #b38928;">
                    عرض وطباعة PDF 🖨️
                </a>
                <a href="{{ route('certificates.verify', $cert->verification_code) }}" target="_blank" class="btn btn-sm" title="التحقق العلني">
                    🔍
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{ $certificates->links() }}
    @endif

</div>
@endsection
