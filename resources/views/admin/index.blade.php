@extends('layouts.app')

@section('title', 'لوحة القيادة العامة — Admin Dashboard')

@section('content')
<div style="display: flex; flex-direction: column; gap: 26px;">

    <!-- Admin Hero Header -->
    <div class="card" style="padding: 30px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(239, 68, 68, 0.1)), var(--bg-elevated); border: 1px solid rgba(245, 158, 11, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="role-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); margin-bottom: 8px; display: inline-block;">
                    👨‍💼 المشرف الأكاديمي العام (Super Administrator)
                </span>
                <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 6px;">
                    لوحة التحكم والإدارة المركزية — QuizArena Governance
                </h1>
                <p style="color: var(--text-muted); font-size: 14px;">
                    متابعة مؤشرات أداء المنصة، حوكمة المستخدمين، وسجل التدقيق الأمني المباشر (Audit Log).
                </p>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('admin.users') }}" class="btn btn-primary">
                    👥 إدارة المستخدمين
                </a>
                <a href="{{ route('admin.auditLogs') }}" class="btn">
                    🛡️ سجل التدقيق
                </a>
                <a href="{{ route('ai.admin') }}" class="btn btn-accent">
                    🤖 تحليلات AI
                </a>
            </div>
        </div>
    </div>

    <!-- Admin KPIs Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
        <div class="card" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; margin-bottom: 4px;">👨‍🎓</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--primary);">{{ $stats['total_students'] }}</div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">الطلاب المسجلين</div>
        </div>

        <div class="card" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; margin-bottom: 4px;">👨‍🏫</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--accent);">{{ $stats['total_teachers'] }}</div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">المعلمين المعتمدين</div>
        </div>

        <div class="card" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; margin-bottom: 4px;">📚</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--success);">{{ $stats['total_courses'] }}</div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">الكورسات المنشورة</div>
        </div>

        <div class="card" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; margin-bottom: 4px;">📝</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--warning);">{{ $stats['total_quizzes'] }}</div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">الاختبارات الذكية</div>
        </div>

        <div class="card" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; margin-bottom: 4px;">📜</div>
            <div style="font-size: 26px; font-weight: 900; color: #a855f7;">{{ $stats['total_certificates'] }}</div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">الشهادات الصادرة</div>
        </div>

        <div class="card" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; margin-bottom: 4px;">🚨</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--danger);">{{ $stats['pending_feedback'] }}</div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">بلاغات قيد المراجعة</div>
        </div>
    </div>

    <!-- Recent Audit Logs Strip (Requirement 21) -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <span>🛡️</span> آخر العمليات المسجلة في سجل التدقيق (Audit Trail)
            </h3>
            <a href="{{ route('admin.auditLogs') }}" class="btn btn-sm">سجل التدقيق الكامل</a>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 10px 12px;">المستخدم</th>
                        <th style="padding: 10px 12px;">العملية (Action)</th>
                        <th style="padding: 10px 12px;">التفاصيل</th>
                        <th style="padding: 10px 12px;">عنوان IP</th>
                        <th style="padding: 10px 12px;">التوقيت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 700;">{{ $log->user?->name ?? 'النظام / زائر' }}</td>
                        <td style="padding: 12px;"><span class="role-pill">{{ $log->action }}</span></td>
                        <td style="padding: 12px; color: var(--text-main);">{{ Str::limit($log->description, 80) }}</td>
                        <td style="padding: 12px; color: var(--text-muted); font-family: monospace;">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                        <td style="padding: 12px; color: var(--text-muted); font-size: 12px;">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
