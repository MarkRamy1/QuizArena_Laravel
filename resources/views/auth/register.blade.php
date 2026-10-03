@extends('layouts.app')

@section('title', __('app.register') . ' — ' . __('app.app_name'))

@section('content')
<style>
    .register-wrapper {
        min-height: 85vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 12px;
    }

    .register-container {
        width: 100%;
        max-width: 1080px;
        background: var(--bg-elevated);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        display: grid;
        grid-template-columns: 400px 1fr;
        position: relative;
    }

    @media (max-width: 920px) {
        .register-container {
            grid-template-columns: 1fr;
            max-width: 600px;
        }
        .register-sidebar {
            display: none !important;
        }
    }

    @media (max-width: 520px) {
        .register-main {
            padding: 24px 16px !important;
        }
        .role-cards-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Left Sidebar Showcase */
    .register-sidebar {
        background: linear-gradient(145deg, rgba(79, 70, 229, 0.25) 0%, rgba(6, 182, 212, 0.15) 100%), var(--surface);
        border-inline-end: 1px solid var(--surface-border);
        padding: 44px 32px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .register-sidebar::before {
        content: '';
        position: absolute;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
        top: -60px;
        right: -60px;
        pointer-events: none;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .brand-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
    }

    .benefit-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 20px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 14px 16px;
        border-radius: var(--radius-md);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .benefit-item:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.06);
    }

    .benefit-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    /* Right Form Area */
    .register-main {
        padding: 40px 36px;
        background: var(--bg-elevated);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Role Radio Cards */
    .role-cards-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 22px;
    }

    .role-option-card {
        border: 2px solid var(--surface-border);
        border-radius: var(--radius-md);
        padding: 16px 14px;
        cursor: pointer;
        background: var(--surface);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 12px;
        user-select: none;
        position: relative;
    }

    .role-option-card:hover {
        border-color: var(--primary-light);
        background: var(--surface-hover);
    }

    .role-option-card.active {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(6, 182, 212, 0.06)), var(--surface);
        box-shadow: 0 0 0 1px var(--primary), 0 4px 16px rgba(79, 70, 229, 0.18);
    }

    .role-option-card input[type="radio"] {
        display: none;
    }

    .role-check-indicator {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid var(--surface-border);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        margin-inline-start: auto;
    }

    .role-option-card.active .role-check-indicator {
        background: var(--primary);
        border-color: var(--primary);
    }

    .role-option-card.active .role-check-indicator::after {
        content: '✓';
        color: #fff;
        font-size: 11px;
        font-weight: 900;
    }

    /* Inputs Styling */
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

    .custom-input::placeholder {
        color: var(--text-muted);
        opacity: 0.6;
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

    .password-toggle-btn:hover {
        color: var(--text-main);
    }

    /* Password Strength Meter */
    .strength-meter {
        height: 4px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 2px;
        margin-top: 6px;
        overflow: hidden;
        display: flex;
    }

    .strength-segment {
        flex: 1;
        height: 100%;
        transition: background 0.3s ease;
        margin-right: 2px;
    }

    .strength-segment:last-child {
        margin-right: 0;
    }

    /* Submit Button */
    .btn-register-submit {
        background: linear-gradient(135deg, var(--primary), #6366f1);
        color: #ffffff;
        border: none;
        border-radius: var(--radius-md);
        padding: 14px 24px;
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
    }

    .btn-register-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.45);
        background: linear-gradient(135deg, var(--primary-light), #4f46e5);
    }

    .btn-register-submit:active {
        transform: translateY(0);
    }

    /* Accordion Section */
    .collapsible-box {
        border: 1px dashed var(--surface-border);
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.02);
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .collapsible-header {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }

    .collapsible-header:hover {
        background: rgba(255, 255, 255, 0.04);
    }

    .collapsible-body {
        padding: 16px;
        border-top: 1px solid var(--surface-border);
        display: none;
    }
</style>

<div class="register-wrapper">
    <div class="register-container">
        <!-- ==================== LEFT SIDEBAR SHOWCASE ==================== -->
        <div class="register-sidebar">
            <div>
                <div class="sidebar-brand">
                    <div class="brand-icon">🎓</div>
                    <div>
                        <div style="font-size: 20px; font-weight: 900; letter-spacing: -0.5px;">{{ __('app.app_name') }}</div>
                        <div style="font-size: 12px; color: var(--accent); font-weight: 700;">{{ __('app.app_tagline') }}</div>
                    </div>
                </div>

                <h2 style="font-size: 24px; font-weight: 900; line-height: 1.4; margin-bottom: 12px;">
                    {{ __('app.reg_benefit_title') }}
                </h2>
                <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 28px;">
                    {{ __('app.reg_benefit_subtitle') }}
                </p>

                <div class="benefit-item">
                    <div class="benefit-icon" style="background: rgba(79, 70, 229, 0.2); color: #818cf8;">⚡</div>
                    <div>
                        <div style="font-weight: 800; font-size: 13.5px;">{{ __('app.reg_benefit_1_title') }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('app.reg_benefit_1_desc') }}</div>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon" style="background: rgba(6, 182, 212, 0.2); color: #22d3ee;">👥</div>
                    <div>
                        <div style="font-weight: 800; font-size: 13.5px;">{{ __('app.reg_benefit_2_title') }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('app.reg_benefit_2_desc') }}</div>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">🤖</div>
                    <div>
                        <div style="font-weight: 800; font-size: 13.5px;">{{ __('app.reg_benefit_3_title') }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('app.reg_benefit_3_desc') }}</div>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">🎮</div>
                    <div>
                        <div style="font-weight: 800; font-size: 13.5px;">{{ __('app.reg_benefit_4_title') }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('app.reg_benefit_4_desc') }}</div>
                    </div>
                </div>
            </div>

            <div style="padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 12.5px; color: var(--text-muted);">{{ __('app.reg_benefit_footer') }}</span>
                <span class="role-pill" style="font-size: 11px;">{{ __('app.reg_benefit_edition') }}</span>
            </div>
        </div>

        <!-- ==================== RIGHT REGISTRATION FORM ==================== -->
        <div class="register-main">
            <div style="margin-bottom: 22px;">
                <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 6px;">{{ __('app.auth_register_title') }} ✨</h1>
                <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.auth_register_subtitle') }}</p>
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

            <form action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf

                <!-- Role Selection Radio Cards -->
                <div style="margin-bottom: 18px;">
                    <label class="form-label" style="font-weight: 800; font-size: 13.5px; margin-bottom: 8px;">{{ __('app.auth_select_role') }}</label>
                    <div class="role-cards-grid">
                        <!-- Student Option -->
                        <label class="role-option-card active" id="studentCard" onclick="selectRole('STUDENT')">
                            <input type="radio" name="role" value="STUDENT" id="roleStudent" checked>
                            <div style="font-size: 28px;">🎓</div>
                            <div>
                                <div style="font-weight: 800; font-size: 14.5px;">{{ __('app.auth_role_student') }}</div>
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ __('app.auth_role_student_hint') }}</div>
                            </div>
                            <div class="role-check-indicator"></div>
                        </label>

                        <!-- Teacher Option -->
                        <label class="role-option-card" id="teacherCard" onclick="selectRole('TEACHER')">
                            <input type="radio" name="role" value="TEACHER" id="roleTeacher">
                            <div style="font-size: 28px;">👨‍🏫</div>
                            <div>
                                <div style="font-weight: 800; font-size: 14.5px;">{{ __('app.auth_role_teacher') }}</div>
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ __('app.auth_role_teacher_hint') }}</div>
                            </div>
                            <div class="role-check-indicator"></div>
                        </label>
                    </div>
                </div>

                <!-- Basic User Info -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">{{ __('app.auth_full_name') }} *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="custom-input"
                                   required
                                   placeholder="Full Name / الاسم">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">{{ __('app.auth_email') }} *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">✉️</span>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="custom-input"
                                   required
                                   placeholder="name@example.com">
                        </div>
                    </div>
                </div>

                <!-- Password and Confirmation -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">{{ __('app.auth_password') }} *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password"
                                   name="password"
                                   id="passwordInput"
                                   class="custom-input"
                                   required
                                   minlength="6"
                                   placeholder="••••••••"
                                   oninput="checkPasswordStrength(this.value)">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('passwordInput', this)">
                                👁️
                            </button>
                        </div>
                        <div class="strength-meter">
                            <div class="strength-segment" id="str1"></div>
                            <div class="strength-segment" id="str2"></div>
                            <div class="strength-segment" id="str3"></div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">{{ __('app.auth_password_confirm') }} *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔑</span>
                            <input type="password"
                                   name="password_confirmation"
                                   id="passwordConfirmInput"
                                   class="custom-input"
                                   required
                                   minlength="6"
                                   placeholder="••••••••">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('passwordConfirmInput', this)">
                                👁️
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Student Block: Teacher Code Entry -->
                <div id="studentFieldsBlock" style="margin-bottom: 16px;">
                    <div style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.08), rgba(79, 70, 229, 0.05)), var(--surface); border: 1px dashed var(--accent); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span style="font-size: 18px;">🔗</span>
                            <label style="font-weight: 800; font-size: 13.5px; color: var(--text-main); margin: 0;">
                                {{ __('app.auth_teacher_code_title') }}
                            </label>
                        </div>
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                            {{ __('app.auth_teacher_code_hint') }}
                        </p>
                        <div class="input-wrapper">
                            <span class="input-icon">🎟️</span>
                            <input type="text"
                                   name="teacher_code"
                                   value="{{ old('teacher_code') }}"
                                   class="custom-input"
                                   placeholder="TCH-XXXXX"
                                   style="text-transform: uppercase; font-weight: 800; letter-spacing: 1px;">
                        </div>
                    </div>

                    <!-- Collapsible Guardian Information -->
                    <div class="collapsible-box">
                        <div class="collapsible-header" onclick="toggleGuardianBox()">
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px;">
                                <span>👨‍👧‍👦</span>
                                <span>{{ __('app.auth_guardian_title') }}</span>
                            </div>
                            <span id="guardianArrow" style="font-size: 12px; transition: transform 0.2s ease;">▼</span>
                        </div>
                        <div class="collapsible-body" id="guardianBody">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label class="form-label" style="font-size: 12px;">{{ __('app.auth_guardian_name') }}</label>
                                    <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" class="custom-input" placeholder="Parent Name" style="padding-inline: 14px;">
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 12px;">{{ __('app.auth_guardian_relation') }}</label>
                                    <select name="guardian_relation" class="custom-input" style="padding-inline: 14px;">
                                        <option value="الأب">الأب (Father)</option>
                                        <option value="الأم">الأم (Mother)</option>
                                        <option value="ولي الأمر">ولي الأمر (Guardian)</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                                <div>
                                    <label class="form-label" style="font-size: 12px;">{{ __('app.auth_guardian_phone') }}</label>
                                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" class="custom-input" placeholder="+2010xxxxxxxx" style="padding-inline: 14px;">
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 12px;">{{ __('app.auth_guardian_email') }}</label>
                                    <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" class="custom-input" placeholder="parent@example.com" style="padding-inline: 14px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Teacher Notice Block -->
                <div id="teacherNoticeBlock" style="display: none; background: rgba(79, 70, 229, 0.1); border: 1px solid rgba(79, 70, 229, 0.3); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <span style="font-size: 26px;">🎉</span>
                        <div>
                            <div style="font-weight: 800; font-size: 14px; color: var(--primary-light);">{{ __('app.auth_teacher_notice_title') }}</div>
                            <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; line-height: 1.5;">
                                {{ __('app.auth_teacher_notice_desc') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms Notice -->
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;">
                    {{ __('app.auth_terms_agreement') }}
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-register-submit">
                    <span>{{ __('app.auth_btn_submit_register') }}</span>
                </button>
            </form>

            <div style="text-align: center; margin-top: 22px; font-size: 14px; color: var(--text-muted);">
                {{ __('app.auth_already_registered') }}
                <a href="{{ route('login') }}" style="color: var(--primary-light); font-weight: 800; text-decoration: none;">
                    {{ __('app.login') }} 🔐
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function selectRole(role) {
        const studentCard = document.getElementById('studentCard');
        const teacherCard = document.getElementById('teacherCard');
        const studentRadio = document.getElementById('roleStudent');
        const teacherRadio = document.getElementById('roleTeacher');
        const studentBlock = document.getElementById('studentFieldsBlock');
        const teacherNotice = document.getElementById('teacherNoticeBlock');

        if (role === 'STUDENT') {
            studentRadio.checked = true;
            studentCard.classList.add('active');
            teacherCard.classList.remove('active');
            studentBlock.style.display = 'block';
            teacherNotice.style.display = 'none';
        } else {
            teacherRadio.checked = true;
            teacherCard.classList.add('active');
            studentCard.classList.remove('active');
            studentBlock.style.display = 'none';
            teacherNotice.style.display = 'block';
        }
    }

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

    function checkPasswordStrength(val) {
        const s1 = document.getElementById('str1');
        const s2 = document.getElementById('str2');
        const s3 = document.getElementById('str3');

        // Reset
        s1.style.background = 'rgba(255, 255, 255, 0.08)';
        s2.style.background = 'rgba(255, 255, 255, 0.08)';
        s3.style.background = 'rgba(255, 255, 255, 0.08)';

        if (!val) return;

        if (val.length < 6) {
            s1.style.background = 'var(--danger)';
        } else if (val.length < 9) {
            s1.style.background = 'var(--warning)';
            s2.style.background = 'var(--warning)';
        } else {
            s1.style.background = 'var(--success)';
            s2.style.background = 'var(--success)';
            s3.style.background = 'var(--success)';
        }
    }

    function toggleGuardianBox() {
        const body = document.getElementById('guardianBody');
        const arrow = document.getElementById('guardianArrow');
        if (body.style.display === 'block') {
            body.style.display = 'none';
            arrow.style.transform = 'rotate(0deg)';
        } else {
            body.style.display = 'block';
            arrow.style.transform = 'rotate(180deg)';
        }
    }
</script>
@endsection
