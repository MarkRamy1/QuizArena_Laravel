@extends('layouts.app')

@section('title', 'إدارة المستخدمين وحوكمة الحسابات — User Management')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header & Search -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>👥</span> إدارة المستخدمين وحوكمة الحسابات
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">التحكم في أدوار المستخدمين، تعديل الصلاحيات، وإيقاف أو تفعيل الحسابات</p>
        </div>

        <a href="{{ route('admin.index') }}" class="btn">
            ⬅ العودة للوحة الإدارة
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card" style="padding: 16px 20px;">
        <form action="{{ route('admin.users') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="ابحث بالاسم، البريد أو الهاتف..."
                   class="form-control"
                   style="max-width: 280px; font-size: 13.5px;">

            <select name="role" class="form-control" style="max-width: 160px; font-size: 13.5px;">
                <option value="">كافة الأدوار</option>
                <option value="STUDENT" {{ request('role') === 'STUDENT' ? 'selected' : '' }}>طالب (Student)</option>
                <option value="TEACHER" {{ request('role') === 'TEACHER' ? 'selected' : '' }}>معلم (Teacher)</option>
                <option value="ADMIN" {{ request('role') === 'ADMIN' ? 'selected' : '' }}>مشرف (Admin)</option>
            </select>

            <select name="status" class="form-control" style="max-width: 160px; font-size: 13.5px;">
                <option value="">كافة الحالات</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>نشط (Active)</option>
                <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>موقوف (Suspended)</option>
            </select>

            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                تصفية 🔍
            </button>
            <a href="{{ route('admin.users') }}" class="btn" style="padding: 10px 16px;">
                إلغاء التصفية
            </a>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="card">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 12px;">المستخدم</th>
                        <th style="padding: 12px;">البريد الإلكتروني</th>
                        <th style="padding: 12px;">الدور الحالي</th>
                        <th style="padding: 12px;">حالة الحساب</th>
                        <th style="padding: 12px;">تاريخ التسجيل</th>
                        <th style="padding: 12px; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $u->avatar_url }}" style="width: 32px; height: 32px; border-radius: 50%;">
                                <div>
                                    <div style="font-weight: 700;">{{ $u->name }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $u->phone ?? 'بدون هاتف' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px; font-family: monospace;">{{ $u->email }}</td>
                        <td style="padding: 12px;">
                            <!-- Change Role Inline Form -->
                            <form action="{{ route('admin.users.changeRole', $u->id) }}" method="POST" style="display: inline-flex; align-items: center; gap: 4px;">
                                @csrf
                                <select name="role" onchange="this.form.submit()" class="form-control" style="padding: 4px 8px; font-size: 12px; width: auto;">
                                    <option value="STUDENT" {{ $u->role === 'STUDENT' ? 'selected' : '' }}>🎓 طالب</option>
                                    <option value="TEACHER" {{ $u->role === 'TEACHER' ? 'selected' : '' }}>👨‍🏫 معلم</option>
                                    <option value="ADMIN" {{ $u->role === 'ADMIN' ? 'selected' : '' }}>👨‍💼 مشرف</option>
                                </select>
                            </form>
                        </td>
                        <td style="padding: 12px;">
                            @if($u->status === 'ACTIVE')
                            <span class="role-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
                                نشط (Active)
                            </span>
                            @else
                            <span class="role-pill" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.3);">
                                موقوف (Suspended)
                            </span>
                            @endif
                        </td>
                        <td style="padding: 12px; color: var(--text-muted); font-size: 12px;">{{ $u->created_at->format('Y-m-d') }}</td>
                        <td style="padding: 12px; text-align: center;">
                            @if($u->id !== Auth::id())
                            <form action="{{ route('admin.users.toggleStatus', $u->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @if($u->status === 'ACTIVE')
                                <button type="submit" class="btn btn-sm btn-danger" style="font-size: 12px; padding: 4px 10px;" onclick="return confirm('هل تريد إيقاف حساب هذا المستخدم؟');">
                                    إيقاف 🛑
                                </button>
                                @else
                                <button type="submit" class="btn btn-sm btn-success" style="font-size: 12px; padding: 4px 10px;">
                                    تنشيط ✓
                                </button>
                                @endif
                            </form>
                            @else
                            <span style="font-size: 12px; color: var(--text-muted);">حسابك الحالي</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">لا يوجد مستخدمون يطابقون شروط البحث.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
