@extends('layouts.app')

@section('title', 'سجل التدقيق الأمني والعمليات — Admin Audit Log')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>🛡️</span> سجل التدقيق الأمني والعمليات (Audit Log)
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">توثيق غير قابل للتعديل لكافة الأنشطة الحساسة: إنشاء الاختبارات، التسليمات، وتعديل الحسابات</p>
        </div>

        <a href="{{ route('admin.index') }}" class="btn">
            ⬅ العودة للوحة الإدارة
        </a>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 12px;">المستخدم (User)</th>
                        <th style="padding: 12px;">نوع العملية (Action)</th>
                        <th style="padding: 12px;">تفاصيل النشاط (Description)</th>
                        <th style="padding: 12px;">عنوان IP</th>
                        <th style="padding: 12px;">التوقيت الكامل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 700;">
                            @if($log->user)
                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                    <img src="{{ $log->user->avatar_url }}" style="width: 22px; height: 22px; border-radius: 50%;">
                                    {{ $log->user->name }}
                                </span>
                            @else
                                <span style="color: var(--text-muted);">نظام / زائر</span>
                            @endif
                        </td>
                        <td style="padding: 12px;">
                            <span class="role-pill">{{ $log->action }}</span>
                        </td>
                        <td style="padding: 12px; color: var(--text-main);">
                            {{ $log->description }}
                        </td>
                        <td style="padding: 12px; font-family: monospace; font-size: 12px; color: var(--text-muted);">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td style="padding: 12px; color: var(--text-muted); font-size: 12px;">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                            <div style="font-size: 11px; opacity: 0.8;">({{ $log->created_at->diffForHumans() }})</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">لا توجد سجلات تدقيق مسجلة حتى الآن.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
