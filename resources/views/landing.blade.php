@extends('layouts.app')

@section('title', __('app.app_name') . ' — ' . __('app.hero_subhead'))

@section('content')
<style>
    /* Landing Hero Styles */
    .hero-section {
        position: relative;
        padding: 50px 0 60px;
        text-align: center;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        border-radius: 999px;
        background: rgba(79, 70, 229, 0.15);
        border: 1px solid rgba(79, 70, 229, 0.35);
        color: var(--primary-light);
        font-size: 13.5px;
        font-weight: 800;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px var(--primary-glow);
    }

    .hero-title {
        font-size: clamp(32px, 5vw, 54px);
        font-weight: 900;
        line-height: 1.25;
        letter-spacing: -1px;
        margin-bottom: 20px;
    }

    .hero-gradient-text {
        background: linear-gradient(135deg, #6366f1 0%, #06b6d4 50%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: clamp(16px, 2vw, 19px);
        color: var(--text-muted);
        max-width: 820px;
        margin: 0 auto 36px;
        line-height: 1.8;
    }

    /* Quick Quiz Join Box */
    .quiz-join-card {
        max-width: 620px;
        margin: 0 auto 48px;
        background: var(--bg-elevated);
        border: 2px solid var(--primary);
        border-radius: var(--radius-xl);
        padding: 24px 28px;
        box-shadow: 0 12px 36px var(--primary-glow);
        position: relative;
    }

    .join-input-group {
        display: flex;
        gap: 12px;
        margin-top: 14px;
    }

    @media (max-width: 640px) {
        .join-input-group {
            flex-direction: column;
        }
    }

    /* Stats Grid */
    .stats-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 18px;
        margin-bottom: 60px;
    }

    .stat-box {
        background: var(--bg-elevated);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: 22px;
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 900;
        background: linear-gradient(135deg, var(--primary-light), var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 4px;
    }

    /* Roles Section */
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
        gap: 24px;
        margin-bottom: 60px;
    }

    .role-card {
        background: var(--bg-elevated);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-xl);
        padding: 30px;
        display: flex;
        flex-direction: column;
        transition: all 0.25s;
        position: relative;
        overflow: hidden;
    }

    .role-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .role-card.student::before { background: linear-gradient(90deg, #4f46e5, #06b6d4); }
    .role-card.teacher::before { background: linear-gradient(90deg, #06b6d4, #10b981); }
    .role-card.admin::before { background: linear-gradient(90deg, #f59e0b, #ef4444); }

    .role-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .role-icon-bubble {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 18px;
    }

    .feature-pill-list {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .feature-pill-list li {
        font-size: 14px;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Features Grid */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 22px;
        margin-bottom: 60px;
    }

    .feature-card {
        background: var(--bg-elevated);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: 24px;
        transition: all 0.2s;
    }

    .feature-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    /* Demo Quick Box */
    .demo-bar {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
        border: 1px solid rgba(79, 70, 229, 0.3);
        border-radius: var(--radius-xl);
        padding: 28px;
        text-align: center;
        margin-bottom: 40px;
    }
</style>

<div class="hero-section">
    <!-- Hero Header -->
    <div class="hero-badge">
        <span>🚀</span> {{ __('app.hero_badge') }}
    </div>

    <h1 class="hero-title">
        {{ __('app.hero_title') }} <br>
        <span class="hero-gradient-text">{{ __('app.hero_subhead') }}</span>
    </h1>

    <p class="hero-subtitle">
        {{ __('app.hero_desc') }}
    </p>

    <!-- Guest Primary Call-To-Action (Login & Register) -->
    @guest
    <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.16) 0%, rgba(6, 182, 212, 0.12) 100%); border: 2px solid rgba(79, 70, 229, 0.35); border-radius: var(--radius-xl); padding: 30px 24px; max-width: 680px; margin: 0 auto 36px; box-shadow: 0 16px 40px rgba(79, 70, 229, 0.2); backdrop-filter: blur(12px);">
        <div style="font-size: 20px; font-weight: 900; color: var(--text-main); margin-bottom: 8px;">
            {{ __('app.guest_welcome_title') }}
        </div>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 22px;">
            {{ __('app.guest_welcome_desc') }}
        </p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 15px 36px; font-size: 16px; font-weight: 900; border-radius: 14px; box-shadow: 0 8px 24px var(--primary-glow); display: inline-flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">🔐</span>
                <span>{{ __('app.btn_login_account') }}</span>
            </a>
            <a href="{{ route('register') }}" class="btn btn-accent" style="padding: 15px 36px; font-size: 16px; font-weight: 900; border-radius: 14px; box-shadow: 0 8px 24px rgba(6, 182, 212, 0.3); display: inline-flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">✨</span>
                <span>{{ __('app.btn_register_new') }}</span>
            </a>
        </div>
    </div>
    @endguest

    @auth
    <!-- Logged In User Quick Action Buttons -->
    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-bottom: 36px;">
        <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 14px 32px; font-size: 16px;">
            📊 {{ __('app.btn_my_dashboard') }}
        </a>
        <a href="{{ route('quizzes.index') }}" class="btn" style="padding: 14px 28px; font-size: 16px;">
            📝 {{ __('app.btn_explore_quizzes') }}
        </a>
        <a href="{{ route('certificates.verify') }}" class="btn" style="padding: 14px 24px; font-size: 15px;">
            🔍 {{ __('app.btn_verify_certificate') }}
        </a>
    </div>
    @endauth

    <!-- Quick Quiz Join Box -->
    <div class="quiz-join-card">
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 800; font-size: 15px; color: var(--text-main);">
            <span>⚡</span> {{ __('app.join_code_title') }}
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
            {{ __('app.join_code_desc') }}
        </p>

        <form action="{{ route('landing.joinQuiz') }}" method="POST">
            @csrf
            <div class="join-input-group">
                <input type="text"
                       name="code"
                       placeholder="{{ __('app.join_code_placeholder') }}"
                       class="form-control"
                       style="font-size: 16px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;"
                       required>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; white-space: nowrap;">
                    {{ __('app.join_code_btn') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Social Proof Stats Strip -->
<div class="stats-strip">
    <div class="stat-box">
        <div class="stat-number">{{ number_format($stats['students_count']) }}+</div>
        <div style="font-weight: 700; font-size: 14px;">{{ __('app.stat_active_students') }}</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ __('app.stat_learn_daily') }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-number">{{ number_format($stats['courses_count']) }}+</div>
        <div style="font-weight: 700; font-size: 14px;">{{ __('app.stat_courses') }}</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ __('app.stat_various_disciplines') }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-number">{{ number_format($stats['quizzes_count']) }}+</div>
        <div style="font-weight: 700; font-size: 14px;">{{ __('app.stat_quizzes') }}</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ __('app.stat_progressive_difficulty') }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-number">{{ number_format($stats['certificates_count']) }}+</div>
        <div style="font-weight: 700; font-size: 14px;">{{ __('app.stat_certificates') }}</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ __('app.stat_official_verification') }}</div>
    </div>
</div>

<!-- Three Roles Breakdown -->
<div style="text-align: center; margin-bottom: 30px;">
    <h2 style="font-size: 28px; font-weight: 900;">{{ __('app.roles_section_title') }}</h2>
    <p style="color: var(--text-muted); font-size: 15px; margin-top: 6px;">{{ __('app.roles_section_desc') }}</p>
</div>

<div class="roles-grid">
    <!-- Student Role Card -->
    <div class="role-card student">
        <div class="role-icon-bubble" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;">
            👨‍🎓
        </div>
        <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 6px;">{{ __('app.role_student_card_title') }}</h3>
        <p style="font-size: 13.5px; color: var(--text-muted);">
            {{ __('app.role_student_card_desc') }}
        </p>

        <ul class="feature-pill-list">
            <li><span>✓</span> {{ __('app.role_student_b1') }}</li>
            <li><span>✓</span> {{ __('app.role_student_b2') }}</li>
            <li><span>✓</span> {{ __('app.role_student_b3') }}</li>
            <li><span>✓</span> {{ __('app.role_student_b4') }}</li>
            <li><span>✓</span> {{ __('app.role_student_b5') }}</li>
            <li><span>✓</span> {{ __('app.role_student_b6') }}</li>
        </ul>

        <a href="{{ route('register') }}" class="btn btn-sm btn-primary" style="margin-top: auto;">
            {{ __('app.role_student_cta') }}
        </a>
    </div>

    <!-- Teacher Role Card -->
    <div class="role-card teacher">
        <div class="role-icon-bubble" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee;">
            👨‍🏫
        </div>
        <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 6px;">{{ __('app.role_teacher_card_title') }}</h3>
        <p style="font-size: 13.5px; color: var(--text-muted);">
            {{ __('app.role_teacher_card_desc') }}
        </p>

        <ul class="feature-pill-list">
            <li><span>✓</span> {{ __('app.role_teacher_b1') }}</li>
            <li><span>✓</span> {{ __('app.role_teacher_b2') }}</li>
            <li><span>✓</span> {{ __('app.role_teacher_b3') }}</li>
            <li><span>✓</span> {{ __('app.role_teacher_b4') }}</li>
            <li><span>✓</span> {{ __('app.role_teacher_b5') }}</li>
            <li><span>✓</span> {{ __('app.role_teacher_b6') }}</li>
        </ul>

        <a href="{{ route('register') }}" class="btn btn-sm" style="margin-top: auto; border-color: var(--accent); color: var(--accent);">
            {{ __('app.role_teacher_cta') }}
        </a>
    </div>

    <!-- Admin Role Card -->
    <div class="role-card admin">
        <div class="role-icon-bubble" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
            👨‍💼
        </div>
        <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 6px;">{{ __('app.role_admin_card_title') }}</h3>
        <p style="font-size: 13.5px; color: var(--text-muted);">
            {{ __('app.role_admin_card_desc') }}
        </p>

        <ul class="feature-pill-list">
            <li><span>✓</span> {{ __('app.role_admin_b1') }}</li>
            <li><span>✓</span> {{ __('app.role_admin_b2') }}</li>
            <li><span>✓</span> {{ __('app.role_admin_b3') }}</li>
            <li><span>✓</span> {{ __('app.role_admin_b4') }}</li>
            <li><span>✓</span> {{ __('app.role_admin_b5') }}</li>
            <li><span>✓</span> {{ __('app.role_admin_b6') }}</li>
        </ul>

        <a href="{{ route('login') }}" class="btn btn-sm" style="margin-top: auto; border-color: var(--warning); color: #fbbf24;">
            {{ __('app.role_admin_cta') }}
        </a>
    </div>
</div>

<!-- Core System Pillars -->
<div style="text-align: center; margin-bottom: 30px;">
    <h2 style="font-size: 28px; font-weight: 900;">{{ __('app.features_section_title') }}</h2>
    <p style="color: var(--text-muted); font-size: 15px; margin-top: 6px;">{{ __('app.features_section_desc') }}</p>
</div>

<div class="features-grid">
    <div class="feature-card">
        <div style="font-size: 32px; margin-bottom: 12px;">🤖</div>
        <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ __('app.feat_ai_title') }}</h4>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
            {{ __('app.feat_ai_desc') }}
        </p>
    </div>

    <div class="feature-card">
        <div style="font-size: 32px; margin-bottom: 12px;">🛡️</div>
        <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ __('app.feat_security_title') }}</h4>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
            {{ __('app.feat_security_desc') }}
        </p>
    </div>

    <div class="feature-card">
        <div style="font-size: 32px; margin-bottom: 12px;">🎮</div>
        <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ __('app.feat_gamify_title') }}</h4>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
            {{ __('app.feat_gamify_desc') }}
        </p>
    </div>

    <div class="feature-card">
        <div style="font-size: 32px; margin-bottom: 12px;">📜</div>
        <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ __('app.feat_cert_title') }}</h4>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
            {{ __('app.feat_cert_desc') }}
        </p>
    </div>

    <div class="feature-card">
        <div style="font-size: 32px; margin-bottom: 12px;">⚡</div>
        <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ __('app.feat_battle_title') }}</h4>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
            {{ __('app.feat_battle_desc') }}
        </p>
    </div>

    <div class="feature-card">
        <div style="font-size: 32px; margin-bottom: 12px;">🎯</div>
        <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ __('app.feat_learn_title') }}</h4>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
            {{ __('app.feat_learn_desc') }}
        </p>
    </div>
</div>

<!-- Call to Action Banner -->
<div class="demo-bar" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(6, 182, 212, 0.08)), var(--surface); border: 1px solid rgba(79, 70, 229, 0.25); border-radius: var(--radius-lg); padding: 36px 20px; text-align: center; margin-top: 40px;">
    <div style="font-size: 26px; font-weight: 900; margin-bottom: 8px;">{{ __('app.cta_bottom_title') }}</div>
    <p style="color: var(--text-muted); font-size: 14.5px; max-width: 600px; margin: 0 auto 24px;">
        {{ __('app.cta_bottom_desc') }}
    </p>

    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
        <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 800;">
            ✨ {{ __('app.register') }}
        </a>
        <a href="{{ route('login') }}" class="btn btn-accent" style="padding: 12px 28px; font-size: 15px; font-weight: 800;">
            🔐 {{ __('app.login') }}
        </a>
    </div>
</div>
@endsection
