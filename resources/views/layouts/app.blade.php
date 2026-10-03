<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.app_name')) — {{ __('app.app_name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @else
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @endif

    <style>
        :root {
            --font-family: @if(app()->getLocale() === 'ar') 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif @else 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif @endif;
            --bg: #090d16;
            --bg-elevated: #0f172a;
            --surface: #131d36;
            --surface-hover: #1c2a4d;
            --surface-border: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-glow: rgba(79, 70, 229, 0.25);
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-xl: 24px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.25);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.35);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.45);
        }

        [data-theme="light"] {
            --bg: #f8fafc;
            --bg-elevated: #ffffff;
            --surface: #ffffff;
            --surface-hover: #f1f5f9;
            --surface-border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #4f46e5;
            --primary-light: #4338ca;
            --primary-glow: rgba(79, 70, 229, 0.12);
            --shadow-sm: 0 2px 8px rgba(15,23,42,0.06);
            --shadow-md: 0 8px 20px rgba(15,23,42,0.08);
            --shadow-lg: 0 16px 36px rgba(15,23,42,0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.1) 0px, transparent 40%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.08) 0px, transparent 40%);
            background-attachment: fixed;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Topbar Header */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--surface-border);
            height: 70px;
            display: flex;
            align-items: center;
        }

        [data-theme="light"] .app-header {
            background: rgba(255, 255, 255, 0.9);
        }

        .header-container {
            width: min(1380px, 94%);
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-badge {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .app-header {
                height: auto;
                padding: 10px 0;
            }
            .header-container {
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
            }
            .header-nav {
                width: 100%;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        /* Language Toggle Button */
        .lang-switch {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--surface);
            border: 1px solid var(--surface-border);
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .lang-switch:hover {
            border-color: var(--primary);
            background: var(--surface-hover);
        }

        /* User Menu */
        .user-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--surface);
            border: 1px solid var(--surface-border);
            padding: 6px 14px 6px 8px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-dropdown-btn:hover {
            border-color: var(--primary);
            background: var(--surface-hover);
        }

        .user-avatar-mini {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .role-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(79, 70, 229, 0.15);
            color: var(--primary-light);
            border: 1px solid rgba(79, 70, 229, 0.3);
        }

        /* Layout Container */
        .main-wrapper {
            width: min(1380px, 94%);
            margin: auto;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 28px;
            padding: 28px 0;
            flex: 1;
        }

        .main-wrapper.no-sidebar {
            display: block;
            width: min(1380px, 94%);
            margin: 0 auto;
            padding: 24px 0;
        }

        .main-wrapper.no-sidebar .content-wrapper {
            width: 100%;
            max-width: 100%;
        }

        /* Mobile Hamburger & Responsive Navigation */
        .mobile-menu-toggle {
            display: none !important;
        }

        .mobile-sidebar-header {
            display: none;
        }

        .sidebar-backdrop {
            display: none;
        }

        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: inline-flex !important;
            }
            .mobile-sidebar-header {
                display: flex !important;
            }
            .main-wrapper {
                grid-template-columns: 1fr !important;
                gap: 20px;
                padding: 16px 0;
            }
            .sidebar-area {
                position: fixed;
                top: 0;
                bottom: 0;
                inset-inline-start: -320px;
                width: 290px;
                max-width: 85vw;
                z-index: 1000;
                background: var(--bg-elevated);
                box-shadow: 0 0 50px rgba(0,0,0,0.6);
                transition: inset-inline-start 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
                padding: 20px 16px;
            }
            .sidebar-area.open {
                inset-inline-start: 0;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.65);
                backdrop-filter: blur(4px);
                z-index: 999;
            }
            .sidebar-backdrop.active {
                display: block;
            }
            .sidebar-card {
                position: static;
                border: none;
                padding: 0;
                background: transparent;
                box-shadow: none;
            }
        }

        /* Universal Responsive Rules for Mobile Phone & Tablet */
        @media (max-width: 768px) {
            div[style*="minmax(360px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(320px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(310px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(300px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(280px"] { grid-template-columns: 1fr !important; }
            .roles-grid { grid-template-columns: 1fr !important; }
            .features-grid { grid-template-columns: 1fr !important; }
            .podium-grid { grid-template-columns: 1fr !important; }
        }

        @media (max-width: 480px) {
            div[style*="minmax(240px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(220px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(200px"] { grid-template-columns: 1fr !important; }
            div[style*="minmax(190px"] { grid-template-columns: 1fr !important; }
            .stats-strip { grid-template-columns: 1fr !important; }
            .card { padding: 16px !important; }
        }

        /* Sidebar Navigation */
        .sidebar-card {
            background: var(--bg-elevated);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-lg);
            padding: 18px;
            height: fit-content;
            position: sticky;
            top: 94px;
            box-shadow: var(--shadow-sm);
        }

        .nav-group-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            padding: 10px 12px 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 14.5px;
            color: var(--text-muted);
            transition: all 0.2s;
            margin-bottom: 4px;
        }

        .nav-item:hover, .nav-item.active {
            color: var(--text-main);
            background: var(--surface);
            border: 1px solid var(--surface-border);
        }

        .nav-item.active {
            border-color: var(--primary);
            background: rgba(79, 70, 229, 0.12);
            color: #fff;
        }

        [data-theme="light"] .nav-item.active {
            color: var(--primary);
            background: rgba(79, 70, 229, 0.08);
        }

        .nav-icon {
            font-size: 18px;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-badge-count {
            margin-inline-start: auto;
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
        }

        /* Common Elements & Utilities */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: 1px solid var(--surface-border);
            background: var(--surface);
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: var(--primary);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff;
            border: none;
            box-shadow: 0 4px 14px var(--primary-glow);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }

        .btn-accent {
            background: var(--accent);
            color: #fff;
            border: none;
        }

        .btn-success {
            background: var(--success);
            color: #fff;
            border: none;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
            border: none;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12.5px;
            border-radius: var(--radius-sm);
        }

        .card {
            background: var(--bg-elevated);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }

        /* Form styling */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            border: 1px solid var(--surface-border);
            background: var(--surface);
            color: var(--text-main);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        /* Alerts */
        .alert-box {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-box.success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .alert-box.error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .alert-box.info {
            background: rgba(6, 182, 212, 0.12);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #38bdf8;
        }

        /* Footer */
        .app-footer {
            border-top: 1px solid var(--surface-border);
            background: var(--bg-elevated);
            padding: 24px 0;
            margin-top: auto;
            font-size: 13.5px;
            color: var(--text-muted);
            text-align: center;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Top Header -->
    <header class="app-header">
        <div class="header-container">
            <div style="display: flex; align-items: center; gap: 10px;">
                @auth
                <button type="button" class="btn btn-sm mobile-menu-toggle" onclick="toggleMobileSidebar()" title="{{ __('app.mobile_menu') }}" style="width: 38px; height: 38px; padding: 0; font-size: 18px; align-items: center; justify-content: center;">
                    ☰
                </button>
                @endauth
                <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="brand-logo">
                    <div class="brand-badge">🎓</div>
                    <span>{{ __('app.app_name') }}</span>
                </a>
            </div>

            <div class="header-nav" style="flex: 1; justify-content: flex-end; gap: 14px;">
                @auth
                <!-- Global Search Input in Header -->
                <form action="{{ route('search') }}" method="GET" style="position: relative; max-width: 320px; width: 100%;">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="{{ __('app.search_placeholder_header') }}"
                           class="form-control"
                           style="padding: 7px 14px 7px 36px; font-size: 13px; border-radius: 999px; height: 36px;">
                </form>

                <!-- Notifications Center Bell -->
                @php
                    $unreadNotifsCount = Auth::user()->inAppNotifications()->where('is_read', false)->count();
                @endphp
                <a href="{{ route('notifications.index') }}" class="btn btn-sm" style="position: relative; padding: 7px 11px; border-radius: 50%; width: 36px; height: 36px;" title="{{ __('app.nav_notifications_center') }}">
                    🔔
                    @if($unreadNotifsCount > 0)
                    <span style="position: absolute; top: -3px; right: -3px; background: var(--danger); color: #fff; font-size: 10px; font-weight: 900; border-radius: 999px; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 0 6px var(--danger);">
                        {{ $unreadNotifsCount > 9 ? '9+' : $unreadNotifsCount }}
                    </span>
                    @endif
                </a>
                @endauth

                <!-- Language Switcher Button -->
                @if(app()->getLocale() === 'ar')
                <a href="{{ route('locale.set', 'en') }}" class="lang-switch" title="Switch to English">
                    🌐 <span>English</span>
                </a>
                @else
                <a href="{{ route('locale.set', 'ar') }}" class="lang-switch" title="التحويل إلى العربية">
                    🌐 <span>العربية</span>
                </a>
                @endif

                <!-- Theme Toggle Button -->
                <button type="button" class="btn btn-sm" onclick="toggleTheme()" title="Toggle Theme" style="padding: 7px 11px; width: 36px; height: 36px; border-radius: 50%;">
                    🌓
                </button>

                @auth
                <!-- User Profile & Action Menu -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ route('profile.edit') }}" class="user-dropdown-btn">
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="user-avatar-mini">
                        <span style="font-weight: 700; font-size: 13.5px;">{{ Str::limit(Auth::user()->name, 16) }}</span>
                        <span class="role-pill" style="@if(Auth::user()->isAdmin()) background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); @elseif(Auth::user()->isTeacher()) background: rgba(6, 182, 212, 0.15); color: #22d3ee; border-color: rgba(6, 182, 212, 0.3); @endif">
                            @if(Auth::user()->isAdmin())
                                👨‍💼 {{ __('app.role_admin') }}
                            @elseif(Auth::user()->isTeacher())
                                👨‍🏫 {{ __('app.role_teacher') }}
                            @else
                                👨‍🎓 {{ __('app.role_student') }}
                            @endif
                        </span>
                    </a>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('app.logout') }}" style="width: 36px; height: 36px; padding: 0;">
                            🚪
                        </button>
                    </form>
                </div>
                @else
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 9px 20px; font-size: 13.5px; font-weight: 800; border-radius: var(--radius-md); box-shadow: 0 4px 14px var(--primary-glow); display: inline-flex; align-items: center; gap: 6px;">
                        <span>🔐</span> <span>{{ __('app.login') }}</span>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-accent" style="padding: 9px 20px; font-size: 13.5px; font-weight: 800; border-radius: var(--radius-md); box-shadow: 0 4px 14px rgba(6, 182, 212, 0.25); display: inline-flex; align-items: center; gap: 6px;">
                        <span>✨</span> <span>{{ __('app.register') }}</span>
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>

    <!-- Main Body Layout -->
    <div class="main-wrapper {{ Auth::check() ? '' : 'no-sidebar' }}">
        @auth
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar-area" id="mainSidebar">
            <div class="sidebar-card">
                <!-- Mobile Sidebar Close Header -->
                <div class="mobile-sidebar-header" style="justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--surface-border);">
                    <div style="font-weight: 800; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>🎓</span> <span>{{ __('app.app_name') }}</span>
                    </div>
                    <button type="button" class="btn btn-sm" onclick="toggleMobileSidebar()" style="width: 32px; height: 32px; padding: 0; border-radius: 50%;">
                        ✕
                    </button>
                </div>

                <nav>
                    <div class="nav-group-title">{{ __('app.nav_home') }}</div>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span>
                        <span>{{ __('app.dashboard') }}</span>
                    </a>

                    @if(Auth::user()->isAdmin())
                    <!-- Admin Specific Navigation -->
                    <div class="nav-group-title">{{ __('app.nav_admin_section') }}</div>
                    <a href="{{ route('admin.teachers') }}" class="nav-item {{ request()->routeIs('admin.teachers*') ? 'active' : '' }}">
                        <span class="nav-icon">👨‍🏫</span>
                        <span>{{ __('app.nav_teachers_students') }}</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span>
                        <span>{{ __('app.nav_user_management') }}</span>
                    </a>
                    <a href="{{ route('admin.courses') }}" class="nav-item {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">
                        <span class="nav-icon">📚</span>
                        <span>{{ __('app.nav_review_courses') }}</span>
                    </a>
                    <a href="{{ route('admin.quizzes') }}" class="nav-item {{ request()->routeIs('admin.quizzes*') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>{{ __('app.nav_quiz_management') }}</span>
                    </a>
                    <a href="{{ route('admin.auditLogs') }}" class="nav-item {{ request()->routeIs('admin.auditLogs*') ? 'active' : '' }}">
                        <span class="nav-icon">🛡️</span>
                        <span>{{ __('app.nav_security_audit') }}</span>
                    </a>
                    <a href="{{ route('admin.feedback') }}" class="nav-item {{ request()->routeIs('admin.feedback*') ? 'active' : '' }}">
                        <span class="nav-icon">🚨</span>
                        <span>{{ __('app.nav_reports_support') }}</span>
                    </a>
                    <a href="{{ route('ai.admin') }}" class="nav-item {{ request()->routeIs('ai.admin*') ? 'active' : '' }}">
                        <span class="nav-icon">🤖</span>
                        <span>{{ __('app.nav_admin_ai_analytics') }}</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        <span class="nav-icon">⚙️</span>
                        <span>{{ __('app.nav_system_settings') }}</span>
                    </a>
                    @elseif(Auth::user()->isTeacher())
                    <!-- Teacher Specific Navigation -->
                    <div class="nav-group-title">{{ __('app.nav_teaching_content') }}</div>
                    <a href="{{ route('courses.index') }}" class="nav-item {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                        <span class="nav-icon">📚</span>
                        <span>{{ __('app.nav_courses_lessons') }}</span>
                    </a>
                    <a href="{{ route('classrooms.index') }}" class="nav-item {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏫</span>
                        <span>{{ __('app.classrooms') }}</span>
                    </a>
                    <a href="{{ route('quizzes.index') }}" class="nav-item {{ request()->routeIs('quizzes.*') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>{{ __('app.nav_quiz_maker') }}</span>
                    </a>
                    <a href="{{ route('live-quiz.index') }}" class="nav-item {{ request()->routeIs('live-quiz.*') ? 'active' : '' }}">
                        <span class="nav-icon">⚡</span>
                        <span>{{ __('app.nav_host_live') }}</span>
                    </a>

                    <div class="nav-group-title">{{ __('app.nav_ai_analytics') }}</div>
                    <a href="{{ route('ai.teacher') }}" class="nav-item {{ request()->routeIs('ai.teacher*') ? 'active' : '' }}">
                        <span class="nav-icon">🤖</span>
                        <span>{{ __('app.nav_teacher_ai_assistant') }}</span>
                    </a>
                    <a href="{{ route('quizzes.analytics') }}" class="nav-item {{ request()->routeIs('quizzes.analytics*') ? 'active' : '' }}">
                        <span class="nav-icon">📈</span>
                        <span>{{ __('app.nav_student_analytics') }}</span>
                    </a>
                    @else
                    <!-- Student Specific Navigation -->
                    <div class="nav-group-title">{{ __('app.nav_learning_lectures') }}</div>
                    <a href="{{ route('courses.index') }}" class="nav-item {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                        <span class="nav-icon">📚</span>
                        <span>{{ __('app.nav_courses_lessons') }}</span>
                    </a>
                    <a href="{{ route('classrooms.index') }}" class="nav-item {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏫</span>
                        <span>{{ __('app.my_classrooms') }}</span>
                    </a>
                    <a href="{{ route('quizzes.index') }}" class="nav-item {{ request()->routeIs('quizzes.*') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>{{ __('app.nav_quiz_arena') }}</span>
                    </a>
                    <a href="{{ route('live-quiz.index') }}" class="nav-item {{ request()->routeIs('live-quiz.*') ? 'active' : '' }}">
                        <span class="nav-icon">⚡</span>
                        <span>{{ __('app.nav_live_quiz') }}</span>
                    </a>
                    <a href="{{ route('battle.index') }}" class="nav-item {{ request()->routeIs('battle.*') ? 'active' : '' }}">
                        <span class="nav-icon">⚔️</span>
                        <span>{{ __('app.nav_battle_1v1') }}</span>
                    </a>

                    <div class="nav-group-title">{{ __('app.nav_ai_gamification') }}</div>
                    <a href="{{ route('ai.tutor') }}" class="nav-item {{ request()->routeIs('ai.tutor*') ? 'active' : '' }}">
                        <span class="nav-icon">🤖</span>
                        <span>{{ __('app.nav_ai_tutor') }}</span>
                    </a>
                    <a href="{{ route('planner.index') }}" class="nav-item {{ request()->routeIs('planner.*') ? 'active' : '' }}">
                        <span class="nav-icon">📅</span>
                        <span>{{ __('app.nav_study_planner') }}</span>
                    </a>
                    <a href="{{ route('leaderboard.index') }}" class="nav-item {{ request()->routeIs('leaderboard.*') ? 'active' : '' }}">
                        <span class="nav-icon">🏆</span>
                        <span>{{ __('app.nav_leaderboard') }}</span>
                    </a>
                    <a href="{{ route('certificates.index') }}" class="nav-item {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
                        <span class="nav-icon">📜</span>
                        <span>{{ __('app.nav_my_certificates') }}</span>
                    </a>
                    @endif

                    <div class="nav-group-title">{{ __('app.nav_communication') }}</div>
                    <a href="{{ route('chat.private') }}" class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                        <span class="nav-icon">💬</span>
                        <span>{{ __('app.nav_direct_messages') }}</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <span class="nav-icon">🔔</span>
                        <span>{{ __('app.nav_notifications_center') }}</span>
                    </a>
                    <a href="{{ route('feedback.index') }}" class="nav-item {{ request()->routeIs('feedback.*') ? 'active' : '' }}">
                        <span class="nav-icon">🚨</span>
                        <span>{{ __('app.nav_send_feedback') }}</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="nav-icon">⚙️</span>
                        <span>{{ __('app.nav_profile_settings') }}</span>
                    </a>
                </nav>
            </div>
        </aside>
        @endauth

        <!-- Primary View Content Area -->
        <main class="content-wrapper" style="min-width: 0;">
            @if(session('success'))
            <div class="alert-box success">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('info'))
            <div class="alert-box info">
                <span>ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="alert-box error">
                <span>❌</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="alert-box error">
                <div>
                    @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="app-footer">
        <div style="width: min(1380px, 94%); margin: auto;">
            {{ __('app.app_name') }} — {{ __('app.app_tagline') }} &bull; &copy; {{ date('Y') }}
        </div>
    </footer>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const target = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('lms_theme', target);
        }

        const savedTheme = localStorage.getItem('lms_theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) {
                sidebar.classList.toggle('open');
            }
            if (backdrop) {
                backdrop.classList.toggle('active');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
