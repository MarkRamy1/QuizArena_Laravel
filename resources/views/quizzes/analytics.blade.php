@extends('layouts.app')

@section('title', 'تحليلات أداء الطلاب ونتائج الاختبارات — Quiz Analytics')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>📈</span> تحليلات أداء الطلاب ونتائج الاختبارات
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">تقييم دقيق لمستويات الفصول، كفاءة الأسئلة، ومؤشرات النزاهة ومكافحة الغش</p>
        </div>

        <a href="{{ route('quizzes.index') }}" class="btn">
            ⬅ العودة للاختبارات
        </a>
    </div>

    <!-- Analytics KPIs -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div class="card" style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 32px; background: rgba(79, 70, 229, 0.12); width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📊
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">متوسط درجات الطلاب</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--primary);">{{ $avgScore }}%</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 32px; background: rgba(16, 185, 129, 0.12); width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                🎯
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">نسبة النجاح العامة</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--success);">{{ $passRate }}%</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 32px; background: rgba(6, 182, 212, 0.12); width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📝
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">إجمالي محاولات الاختبارات</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--accent);">{{ $allAttempts->count() }} محاولة</div>
            </div>
        </div>
    </div>

    <!-- Hardest Questions Section (Requirement 4 & 9) -->
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> أصعب الأسئلة (أعلى تكرار للخطأ لدى الطلاب)
        </h3>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($hardestQuestions as $hq)
            <div style="padding: 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px;">{{ $hq->question_text }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        نوع السؤال: <b>{{ $hq->type }}</b> &bull; درجة السؤال: {{ $hq->points }} pts
                    </div>
                </div>
                <div style="text-align: end;">
                    <span class="role-pill" style="background: rgba(239, 68, 68, 0.15); color: #f87171; font-weight: 800;">
                        أخطأ فيه {{ $hq->incorrect_count }} طلاب
                    </span>
                </div>
            </div>
            @empty
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">لا توجد أخطاء مسجلة، استيعاب الطلاب ممتاز!</p>
            @endforelse
        </div>
    </div>

    <!-- All Quizzes Performance & Export Table -->
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>📋</span> تقارير الاختبارات المنشورة وإمكانية التصدير
        </h3>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 10px 12px;">الاختبار</th>
                        <th style="padding: 10px 12px;">كود الاختبار</th>
                        <th style="padding: 10px 12px;">عدد الأسئلة</th>
                        <th style="padding: 10px 12px;">عدد المحاولات</th>
                        <th style="padding: 10px 12px; text-align: center;">تصدير التقارير (Export)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 700;">{{ $quiz->title }}</td>
                        <td style="padding: 12px; font-family: monospace; color: var(--accent);">{{ $quiz->code }}</td>
                        <td style="padding: 12px;">{{ $quiz->questions_count }} أسئلة</td>
                        <td style="padding: 12px;">{{ $quiz->attempts_count }} محاولة</td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="{{ route('quizzes.exportResults', $quiz->id) }}" class="btn btn-sm btn-primary" title="تحميل التقرير Excel">
                                📊 تصدير Excel (CSV)
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
