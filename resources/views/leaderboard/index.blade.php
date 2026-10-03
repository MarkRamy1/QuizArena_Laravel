@extends('layouts.app')

@section('title', __('app.leaderboard_title') . ' — ' . __('app.app_name'))

@section('content')
<style>
    .podium-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 32px;
        align-items: end;
    }

    @media (max-width: 680px) {
        .podium-grid {
            grid-template-columns: 1fr !important;
            gap: 14px !important;
        }
        .podium-grid .card {
            transform: none !important;
        }
    }
</style>

<div style="max-width: 950px; margin: auto;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-size: clamp(24px, 4vw, 32px); font-weight: 900; margin-bottom: 6px;">🏆 {{ __('app.leaderboard_title') }}</h1>
        <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.leaderboard_subtitle') }}</p>
    </div>

    <!-- Top 3 Podium -->
    @if($leaders->count() >= 3)
    <div class="podium-grid">
        <!-- 2nd Place -->
        @php $second = $leaders[1]; @endphp
        <div class="card" style="text-align: center; padding: 24px 16px; border-color: rgba(191,200,217,0.4); background: linear-gradient(180deg, rgba(191,200,217,0.1), var(--bg-elevated));">
            <div style="font-size: 38px; margin-bottom: 4px;">🥈</div>
            <div style="font-size: 18px; font-weight: 900; color: #bfc8d9;">{{ __('app.second_place') }}</div>
            <h3 style="font-size: 17px; font-weight: 800; margin: 8px 0 4px;">{{ $second->user->name }}</h3>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">{{ __('app.level') }} {{ $second->level }} &bull; 🔥 {{ $second->streak_count ?? 5 }}d</div>
            <span class="pill-stat xp" style="font-size: 14px; font-weight: 900; background: rgba(79, 70, 229, 0.15); color: var(--primary-light); padding: 4px 12px; border-radius: 999px;">{{ $second->xp }} XP</span>
        </div>

        <!-- 1st Place (Highest) -->
        @php $first = $leaders[0]; @endphp
        <div class="card" style="text-align: center; padding: 36px 16px; border: 2px solid #f5c451; background: linear-gradient(180deg, rgba(245,196,81,0.15), var(--bg-elevated)); box-shadow: 0 0 40px rgba(245,196,81,0.25); transform: translateY(-10px);">
            <div style="font-size: 52px; margin-bottom: 4px;">👑</div>
            <div style="font-size: 22px; font-weight: 900; color: #f5c451;">{{ __('app.first_place') }}</div>
            <h3 style="font-size: 20px; font-weight: 900; margin: 8px 0 4px;">{{ $first->user->name }}</h3>
            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 14px;">{{ __('app.level') }} {{ $first->level }} &bull; 🔥 {{ $first->streak_count ?? 12 }}d streak</div>
            <span class="pill-stat xp" style="font-size: 16px; font-weight: 900; padding: 6px 16px; border-radius: 999px; background: rgba(245,196,81,0.2); color: #f5c451;">{{ $first->xp }} XP</span>
        </div>

        <!-- 3rd Place -->
        @php $third = $leaders[2]; @endphp
        <div class="card" style="text-align: center; padding: 20px 16px; border-color: rgba(205,138,82,0.4); background: linear-gradient(180deg, rgba(205,138,82,0.1), var(--bg-elevated));">
            <div style="font-size: 34px; margin-bottom: 4px;">🥉</div>
            <div style="font-size: 17px; font-weight: 900; color: #cd8a52;">{{ __('app.third_place') }}</div>
            <h3 style="font-size: 16px; font-weight: 800; margin: 8px 0 4px;">{{ $third->user->name }}</h3>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">{{ __('app.level') }} {{ $third->level }} &bull; 🔥 {{ $third->streak_count ?? 3 }}d</div>
            <span class="pill-stat xp" style="font-size: 14px; font-weight: 900; background: rgba(205,138,82,0.15); color: #cd8a52; padding: 4px 12px; border-radius: 999px;">{{ $third->xp }} XP</span>
        </div>
    </div>
    @endif

    <!-- Leaderboard Table -->
    <div class="card" style="padding: 24px;">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">{{ __('app.overall_student_ranking') }}</h3>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($leaders as $rank => $leader)
            @php $isMe = ($currentUser && $currentUser->id === $leader->user_id); @endphp
            <div class="card-inner" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; border-radius: var(--radius-md); background: var(--surface); border: 1px solid var(--surface-border); {{ $isMe ? 'border: 2px solid var(--accent); background: rgba(6,182,212,0.08);' : '' }}">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <span style="font-size: 18px; font-weight: 900; width: 30px; text-align: center; color: var(--text-muted);">
                        #{{ $rank + 1 }}
                    </span>
                    <div>
                        <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                            <span>{{ $leader->user->name }}</span>
                            @if($isMe)
                            <span class="pill-stat" style="font-size: 10px; padding: 2px 6px; border-radius: 999px; background: rgba(6,182,212,0.2); color: var(--accent);">{{ __('app.you') }}</span>
                            @endif
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            {{ __('app.level') }} {{ $leader->level }} &bull; {{ __('app.activity_streak') }}: 🔥 {{ $leader->current_streak }} {{ __('app.days_streak') }} &bull; {{ __('app.longest_streak') }}: {{ $leader->longest_streak }}d
                        </div>
                    </div>
                </div>

                <div style="text-align: left;">
                    <span class="pill-stat xp" style="font-size: 13.5px; font-weight: 900; padding: 4px 12px; border-radius: 999px; background: rgba(79, 70, 229, 0.15); color: var(--primary-light);">
                        💎 {{ $leader->xp }} XP
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
