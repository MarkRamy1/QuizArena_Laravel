@extends('layouts.app')

@section('title', 'معارك التحدي 1v1 — Battle Arena')

@section('content')
<div style="max-width: 950px; margin: auto;">
    <!-- Battle Hero Banner -->
    <div class="hero" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(109, 93, 252, 0.2)), var(--card); border-color: rgba(239, 68, 68, 0.4);">
        <div style="text-align: center; max-width: 650px; margin: auto;">
            <div style="font-size: 50px; margin-bottom: 8px;">⚔️</div>
            <h1 style="font-size: 34px; font-weight: 900; margin-bottom: 8px;">ساحة معارك الذكاء 1v1 التفاعلية</h1>
            <p style="font-size: 15px; color: var(--muted); margin-bottom: 24px;">
                تنافس في 3 جولات سريعة ومباشرة ضد الذكاء الاصطناعي (AI Rival)! السرعة والدقة تحددان الفائز لنيل نقاط الخبرة وأوسمة الشرف.
            </p>
            <a href="{{ route('battle.start') }}" class="btn danger" style="padding: 14px 38px; font-size: 18px; box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4);">
                بدء المعركة الآن فوراً ⚡
            </a>
        </div>
    </div>

    <!-- Battle Rules Grid -->
    <div class="grid" style="margin-bottom: 28px;">
        <div class="card" style="text-align: center;">
            <div style="font-size: 30px; margin-bottom: 8px;">⏱️</div>
            <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 4px;">10 ثوانٍ لكل جولة</h4>
            <p style="font-size: 13px; color: var(--muted);">كلما أجبت أسرع، زادت نقاطك في الجولة.</p>
        </div>

        <div class="card" style="text-align: center;">
            <div style="font-size: 30px; margin-bottom: 8px;">🤖</div>
            <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 4px;">منافس ذكي متكافئ</h4>
            <p style="font-size: 13px; color: var(--muted);">يتفاعل الخصم الآلي بمحاكاة سرعة الإنسان الطبيعية.</p>
        </div>

        <div class="card" style="text-align: center;">
            <div style="font-size: 30px; margin-bottom: 8px;">🏆</div>
            <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 4px;">مكافأة الفوز 50 XP</h4>
            <p style="font-size: 13px; color: var(--muted);">تضاف مباشرة إلى رصيدك وترقية مستواك في المنصة.</p>
        </div>
    </div>

    <!-- Recent Battles Table -->
    @if($recentBattles->isNotEmpty())
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">سجل معاركي الأخيرة</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($recentBattles as $b)
            @php
                $myPart = $b->participants->firstWhere('user_id', Auth::id());
                $aiPart = $b->participants->firstWhere('is_ai', true);
                $isWin = ($b->winner_id === Auth::id());
            @endphp
            <div class="card-inner" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px;">
                <div>
                    <span style="font-weight: 800; color: {{ $isWin ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $isWin ? '🏆 فوز ساحق' : 'خسارة' }}
                    </span>
                    <span style="color: var(--muted); font-size: 13px; margin-right: 12px;">
                        كود: <b style="font-family: monospace;">{{ $b->code }}</b>
                    </span>
                </div>
                <div style="font-weight: 800; font-size: 15px;">
                    <span style="color: #4ade80;">أنت: {{ $myPart?->score ?? 0 }}</span>
                    <span style="color: var(--muted); margin: 0 8px;">ضد</span>
                    <span style="color: #f87171;">الخصم: {{ $aiPart?->score ?? 0 }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
