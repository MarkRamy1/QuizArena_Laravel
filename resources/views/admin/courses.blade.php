@extends('layouts.app')

@section('title', 'مراجعة وإدارة الكورسات — Manage Courses')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>📚</span> مراجعة واعتماد الكورسات التعليمية
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">مراجعة محتوى الدورات المنشورة بواسطة المعلمين، التحقق من الجودة، والتحكم بحالة النشر</p>
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
                        <th style="padding: 12px;">الكورس</th>
                        <th style="padding: 12px;">المعلم المؤلف</th>
                        <th style="padding: 12px;">المادة / التخصص</th>
                        <th style="padding: 12px;">عدد الدروس</th>
                        <th style="padding: 12px;">الحالة</th>
                        <th style="padding: 12px; text-align: center;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $c)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 700;">
                            <a href="{{ route('courses.show', $c->id) }}" style="color: inherit; text-decoration: underline;">
                                {{ $c->title }}
                            </a>
                        </td>
                        <td style="padding: 12px;">{{ $c->teacher->name }}</td>
                        <td style="padding: 12px;"><span class="role-pill">{{ $c->subject }}</span></td>
                        <td style="padding: 12px;">{{ $c->lessons->count() }} دروس</td>
                        <td style="padding: 12px;">
                            @if($c->status === 'PUBLISHED')
                            <span class="role-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
                                منشور (Published)
                            </span>
                            @else
                            <span class="role-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3);">
                                مسودة (Draft)
                            </span>
                            @endif
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <form action="{{ route('admin.courses.toggleStatus', $c->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $c->status === 'PUBLISHED' ? 'btn-danger' : 'btn-success' }}">
                                    {{ $c->status === 'PUBLISHED' ? 'إلغاء النشر' : 'اعتماد ونشر ✓' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">لا توجد كورسات في النظام.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $courses->links() }}
        </div>
    </div>

</div>
@endsection
