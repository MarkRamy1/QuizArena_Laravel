@extends('layouts.app')

@section('title', 'إدارة المعلمين وتوزيع الطلاب — Admin')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header -->
    <div class="card" style="padding: 24px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1)), var(--bg-elevated); border: 1px solid var(--surface-border);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <span class="role-pill" style="margin-bottom: 8px; display: inline-block;">👨‍🏫 حوكمة المعلمين والطلاب</span>
                <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 4px;">
                    سجل المعلمين والطلاب التابعين لهم (Teachers & Assigned Students)
                </h1>
                <p style="color: var(--text-muted); font-size: 13.5px;">
                    تتبع كل معلم وكوده الفريد، والاطلاع الفوري على قائمة الطلاب المرتبطين به ونتائجهم.
                </p>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.index') }}" class="btn">⬅ لوحة الإدارة</a>
                <a href="{{ route('admin.users') }}" class="btn btn-primary">👥 كافة المستخدمين</a>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card" style="padding: 16px 20px;">
        <form action="{{ route('admin.teachers') }}" method="GET" style="display: flex; gap: 10px;">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="ابحث باسم المعلم، البريد الإلكتروني، أو كود المعلم (Teacher Code)..."
                   class="form-control"
                   style="font-size: 14px;">
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                بحث 🔍
            </button>
            @if(request('search'))
            <a href="{{ route('admin.teachers') }}" class="btn">إعادة تعيين</a>
            @endif
        </form>
    </div>

    <!-- Teachers List -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @forelse($teachers as $teacher)
        <div class="card" style="padding: 22px;">
            <!-- Teacher Summary Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 52px; height: 52px; border-radius: var(--radius-md); background: rgba(79, 70, 229, 0.15); border: 1px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 26px;">
                        👨‍🏫
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 style="font-size: 17px; font-weight: 800;">{{ $teacher->name }}</h3>
                            <span class="role-pill" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
                                {{ $teacher->status }}
                            </span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">
                            ✉️ {{ $teacher->email }} @if($teacher->phone) &bull; 📞 {{ $teacher->phone }} @endif
                        </div>
                    </div>
                </div>

                <!-- Teacher Code Badge & KPIs -->
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <!-- Teacher Code -->
                    <div style="background: var(--surface); padding: 8px 16px; border-radius: var(--radius-md); border: 2px dashed var(--primary); text-align: center;">
                        <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">كود المعلم (Teacher Code)</div>
                        <div style="font-size: 18px; font-weight: 900; color: var(--primary-light); letter-spacing: 1px;">
                            {{ $teacher->teacher_code ?? $teacher->generateTeacherCode() }}
                        </div>
                    </div>

                    <!-- Students Count -->
                    <div style="text-align: center; min-width: 80px;">
                        <div style="font-size: 20px; font-weight: 900; color: var(--accent);">{{ $teacher->students_count }}</div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">طالباً مسجلاً</div>
                    </div>

                    <!-- Quizzes Count -->
                    <div style="text-align: center; min-width: 80px;">
                        <div style="font-size: 20px; font-weight: 900; color: var(--warning);">{{ $teacher->quizzes_count }}</div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">اختبارات</div>
                    </div>

                    <!-- Toggle Students Button -->
                    <button type="button"
                            onclick="toggleStudentList('students-{{ $teacher->id }}')"
                            class="btn btn-sm btn-primary"
                            style="padding: 8px 14px;">
                        👥 عرض الطلاب ({{ $teacher->students_count }}) ▾
                    </button>
                </div>
            </div>

            <!-- Assigned Students Expandable Container -->
            <div id="students-{{ $teacher->id }}" style="display: none; margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--surface-border);">
                <div style="font-weight: 800; font-size: 14px; margin-bottom: 12px; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                    <span>📋</span> قائمة الطلاب المرتبطين بالمعلم: <b>{{ $teacher->name }}</b>
                </div>

                @if($teacher->students->isEmpty())
                <div style="padding: 16px; background: var(--surface); border-radius: var(--radius-md); text-align: center; color: var(--text-muted); font-size: 13px;">
                    لم يقم أي طالب بالانضمام لهذا المعلم بكود المشاركة بعد.
                </div>
                @else
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--surface-border); text-align: right; color: var(--text-muted); font-size: 12px;">
                                <th style="padding: 8px 12px;">الطالب</th>
                                <th style="padding: 8px 12px;">البريد الإلكتروني</th>
                                <th style="padding: 8px 12px;">ولي الأمر</th>
                                <th style="padding: 8px 12px;">الاختبارات المنجزة</th>
                                <th style="padding: 8px 12px;">تاريخ الانضمام</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacher->students as $student)
                            <tr style="border-bottom: 1px solid var(--surface-border);">
                                <td style="padding: 10px 12px; font-weight: 800;">
                                    👨‍🎓 {{ $student->name }}
                                </td>
                                <td style="padding: 10px 12px; color: var(--text-muted);">
                                    {{ $student->email }}
                                </td>
                                <td style="padding: 10px 12px; font-size: 12.5px;">
                                    @if($student->guardian_name)
                                        {{ $student->guardian_name }} ({{ $student->guardian_relation ?? 'ولي الأمر' }})
                                        <div style="color: var(--text-muted); font-size: 11px;">📞 {{ $student->guardian_phone ?? '-' }}</div>
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td style="padding: 10px 12px; font-weight: 700;">
                                    <span class="role-pill" style="font-size: 11px;">{{ $student->quiz_attempts_count }} محاولة</span>
                                </td>
                                <td style="padding: 10px 12px; color: var(--text-muted); font-size: 12px;">
                                    {{ $student->created_at->format('Y-m-d') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
            <div style="font-size: 40px; margin-bottom: 10px;">👨‍🏫</div>
            <p>لا يوجد معلمون مسجلون في النظام حتى الآن.</p>
            <p style="font-size: 13px; margin-top: 6px;">يمكن للمعلمين التسجيل مباشرة من صفحة إنشاء الحساب أو يمكنك إضافتهم وتعيين أدوارهم.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top: 10px;">
        {{ $teachers->links() }}
    </div>

</div>

<script>
function toggleStudentList(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
}
</script>
@endsection
