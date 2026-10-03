@extends('layouts.app')

@section('title', 'التقويم الدراسي ومواعيد الاختبارات')

@section('content')
<div style="max-width: 950px; margin: auto;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 900; display: flex; align-items: center; gap: 8px;">
            <span>📅</span> التقويم الأكاديمي وجدول المواعيد
        </h1>
        <p style="color: var(--muted); font-size: 14px;">تابع مواعيد إطلاق الاختبارات وجلسات التقييم المقررة لشهر {{ now()->locale('ar')->translatedFormat('F Y') }}</p>
    </div>

    <!-- Calendar Month Card -->
    <div class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 800;">
                {{ now()->locale('ar')->translatedFormat('F Y') }}
            </h3>
            <span class="pill-stat" style="color: var(--secondary);">
                اليوم: {{ now()->locale('ar')->translatedFormat('l, d F Y') }}
            </span>
        </div>

        <!-- Days of Week Header -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; text-align: center; font-weight: 800; font-size: 13px; color: var(--muted); margin-bottom: 8px;">
            <div>السبت</div>
            <div>الأحد</div>
            <div>الإثنين</div>
            <div>الثلاثاء</div>
            <div>الأربعاء</div>
            <div>الخميس</div>
            <div>الجمعة</div>
        </div>

        <!-- Days Grid -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
            @php
                $todayDay = (int) now()->format('j');
                $daysInMonth = (int) now()->daysInMonth;
            @endphp

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php $isToday = ($day === $todayDay); @endphp
            <div class="card-inner" style="min-height: 85px; padding: 10px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;
                {{ $isToday ? 'border-color: var(--secondary); background: rgba(0,217,255,0.08); box-shadow: 0 0 15px rgba(0,217,255,0.15);' : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 900; font-size: 14px; {{ $isToday ? 'color: var(--secondary);' : '' }}">{{ $day }}</span>
                    @if($isToday)
                    <span style="font-size: 10px; background: var(--secondary); color: #000; padding: 1px 5px; border-radius: 4px; font-weight: 800;">اليوم</span>
                    @endif
                </div>

                @if($isToday)
                <div style="font-size: 11px; background: rgba(109,93,252,0.25); color: #b9b2ff; padding: 4px 6px; border-radius: 6px; font-weight: 700;">
                    📝 موعد اختبار اليوم
                </div>
                @elseif($day === $todayDay + 2)
                <div style="font-size: 11px; background: rgba(245,158,11,0.25); color: #fbbf24; padding: 4px 6px; border-radius: 6px; font-weight: 700;">
                    ⚡ جلسة مراجعة
                </div>
                @endif
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection
