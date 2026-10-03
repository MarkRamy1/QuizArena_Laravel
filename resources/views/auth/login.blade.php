@extends('layouts.app')

@section('title', __('app.login') . ' — ' . __('app.app_name'))

@section('content')
<style>
    .login-wrapper {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 14px;
    }

    .login-card {
        width: 100%;
        max-width: 460px;
        background: var(--bg-elevated);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-xl);
        padding: 42px 34px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        position: relative;
        overflow: hidden;
    }

    .login-card::before {
        content: '';
        position: absolute;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(79, 70, 229, 0.2) 0%, transparent 70%);
        top: -50px;
        right: -50px;
        pointer-events: none;
    }

    .login-header-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 16px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        right: 14px;
        color: var(--text-muted);
        font-size: 16px;
        pointer-events: none;
    }

    html[dir="ltr"] .input-icon {
        right: auto;
        left: 14px;
    }

    .custom-input {
        width: 100%;
        background: var(--surface);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        padding: 12px 42px 12px 14px;
        color: var(--text-main);
        font-size: 14.5px;
        font-family: inherit;
        transition: all 0.2s ease;
    }

    html[dir="ltr"] .custom-input {
        padding: 12px 14px 12px 42px;
    }

    .custom-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
        background: var(--bg-elevated);
    }

    .password-toggle-btn {
        position: absolute;
        left: 14px;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 15px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    html[dir="ltr"] .password-toggle-btn {
        left: auto;
        right: 14px;
    }

    .btn-login-submit {
        background: linear-gradient(135deg, var(--primary), #6366f1);
        color: #ffffff;
        border: none;
        border-radius: var(--radius-md);
        padding: 13px 24px;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        width: 100%;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
        margin-top: 10px;
    }

    .btn-login-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.45);
        background: linear-gradient(135deg, var(--primary-light), #4f46e5);
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="login-header-icon">🎓</div>
            <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 6px;">{{ __('app.login') }}</h1>
            <p style="color: var(--text-muted); font-size: 13.5px;">{{ __('app.auth_login_subtitle') }}</p>
        </div>

        @if ($errors->any())
        <div class="alert danger" style="margin-bottom: 20px; padding: 12px 16px; font-size: 13.5px;">
            <ul style="margin: 0; padding-inline-start: 18px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">{{ __('app.email') }}</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉️</span>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="custom-input"
                           required
                           autofocus
                           placeholder="name@example.com">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label">{{ __('app.current_password') }}</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password"
                           name="password"
                           id="loginPassword"
                           class="custom-input"
                           required
                           placeholder="••••••••">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('loginPassword', this)">
                        👁️
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--text-muted);">
                    <input type="checkbox" name="remember" style="width: auto; accent-color: var(--primary);">
                    <span>{{ __('app.auth_remember_me') }}</span>
                </label>
            </div>

            <button type="submit" class="btn-login-submit">
                <span>🔐 {{ __('app.login') }}</span>
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-muted); border-top: 1px solid var(--surface-border); padding-top: 18px;">
            {{ __('app.auth_not_registered_yet') }}
            <a href="{{ route('register') }}" style="color: var(--primary-light); font-weight: 800; text-decoration: none;">
                {{ __('app.register') }} ✨
            </a>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🔒';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    }
</script>
@endsection
