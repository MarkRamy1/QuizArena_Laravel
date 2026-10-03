@extends('layouts.app')

@section('title', 'إدارة الاختبارات وبنوك الأسئلة — Manage Quizzes')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>📝</span> إدارة الاختبارات وبنوك الأسئلة
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">استعراض ومراجعة كافة الاختبارات المنشورة في المنصة والتحقق من التزامها بمعايير النزاهة</p>
        </div>

        <a href="{{ route('admin.index') }}" class="btn">
            ⬅ العودة للوحة الإدارة
        </a>
    </div>

    <div class="card">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 12px;">كود الاختبار</th>
                        <th style="padding: 12px;">عنوان الاختبار</th>
                        <th style="padding: 12px;">المعلم المؤلف</th>
                        <th style="padding: 12px;">المدة</th>
                        <th style="padding: 12px;">درجة النجاح</th>
                        <th style="padding: 12px;">مراقبة النزاهة</th>
                        <th style="padding: 12px; text-align: center;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $q)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 800; font-family: monospace; color: var(--accent);">{{ $q->code }}</td>
                        <td style="padding: 12px; font-weight: 700;">{{ $q->title }}</td>
                        <td style="padding: 12px;">{{ $q->teacher->name }}</td>
                        <td style="padding: 12px;">{{ $q->duration_minutes }} دقيقة</td>
                        <td style="padding: 12px;">{{ $q->passing_score }}%</td>
                        <td style="padding: 12px;">
                            @if($q->is_ai_proctored)
                            <span class="role-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                                ✓ مفعّلة
                            </span>
                            @else
                            <span class="role-pill" style="color: var(--text-muted);">معطلة</span>
                            @endif
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="{{ route('quizzes.show', $q->id) }}" class="btn btn-sm">
                                معاينة الاختبار
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">لا توجد اختبارات مسجلة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $quizzes->links() }}
        </div>
    </div>

</div>
@endsection
