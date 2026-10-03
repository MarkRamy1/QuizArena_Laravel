@extends('layouts.app')

@section('title', __('app.edit_profile'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800;">{{ __('app.my_profile') }} & {{ __('app.settings') }}</h1>
            <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.edit_profile') }} — {{ __('app.preferences') }}</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('locale.set', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="btn btn-sm">
                🌐 {{ app()->getLocale() === 'ar' ? 'Switch to English' : 'التحويل للعربية' }}
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px;">
        <!-- Left: Profile Avatar Card -->
        <div class="card" style="text-align: center;">
            <div style="position: relative; width: 140px; height: 140px; margin: 0 auto 16px;">
                <img id="avatarPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                     style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); box-shadow: var(--shadow-md);">
            </div>

            <h3 style="font-size: 18px; font-weight: 800;">{{ $user->name }}</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">{{ $user->email }}</p>
            <span class="role-pill" style="font-size: 12px; padding: 4px 12px;">
                @if($user->isTeacher()) {{ __('app.role_teacher') }} @else {{ __('app.role_student') }} @endif
            </span>

            <hr style="border: 0; border-top: 1px solid var(--surface-border); margin: 20px 0;">

            <!-- Avatar Upload Form -->
            <form action="{{ route('profile.updateAvatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="text-align: start;">
                    <label class="form-label">{{ __('app.change_avatar') }}</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*" required onchange="previewImage(this)">
                    <small style="color: var(--text-muted); font-size: 11px;">JPG, PNG, WEBP (Max 4MB)</small>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    💾 {{ __('app.save_changes') }}
                </button>
            </form>
        </div>

        <!-- Right: Profile Info & Password Settings -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- General Information & Language -->
            <div class="card">
                <h2 style="font-size: 19px; font-weight: 800; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                    <span>👤</span> {{ __('app.edit_profile') }}
                </h2>

                <form action="{{ route('profile.updateInfo') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('app.email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 890">
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('app.preferred_language') }}</label>
                            <select name="locale" class="form-control" required>
                                <option value="ar" {{ (old('locale', $user->locale) === 'ar' || app()->getLocale() === 'ar') ? 'selected' : '' }}>
                                    🇸🇦 {{ __('app.arabic') }}
                                </option>
                                <option value="en" {{ (old('locale', $user->locale) === 'en' || app()->getLocale() === 'en') ? 'selected' : '' }}>
                                    🇬🇧 {{ __('app.english') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    @if($user->isStudent())
                    <!-- Guardian Details Card -->
                    <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; margin: 18px 0;">
                        <div style="font-weight: 800; font-size: 14px; color: var(--accent); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                            <span>👨‍👧‍👦</span> بيانات ولي الأمر (لإشعارات الغياب ونتائج الامتحانات)
                        </div>
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">
                            تُستخدم هذه البيانات لإرسال تنبيهات تلقائية لولي أمر الطالب عند الغياب عن أي حصة أو اختبار، وتفاصيل درجات الامتحانات عند الانتهاء.
                        </p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 12.5px;">اسم ولي الأمر</label>
                                <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $user->guardian_name) }}" placeholder="مثال: محمود علي">
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="font-size: 12.5px;">صلة القرابة</label>
                                <select name="guardian_relation" class="form-control">
                                    <option value="الأب" {{ old('guardian_relation', $user->guardian_relation) == 'الأب' ? 'selected' : '' }}>الأب</option>
                                    <option value="الأم" {{ old('guardian_relation', $user->guardian_relation) == 'الأم' ? 'selected' : '' }}>الأم</option>
                                    <option value="ولي الأمر" {{ old('guardian_relation', $user->guardian_relation) == 'ولي الأمر' ? 'selected' : '' }}>ولي الأمر / وصي</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 12.5px;">البريد الإلكتروني لولي الأمر</label>
                                <input type="email" name="guardian_email" class="form-control" value="{{ old('guardian_email', $user->guardian_email) }}" placeholder="parent@example.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="font-size: 12.5px;">رقم هاتف / واتساب ولي الأمر</label>
                                <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $user->guardian_phone) }}" placeholder="+201011122233">
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">{{ __('app.bio') }}</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="{{ __('app.bio') }}...">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        💾 {{ __('app.save_changes') }}
                    </button>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="card">
                <h2 style="font-size: 19px; font-weight: 800; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                    <span>🔒</span> {{ __('app.change_password') }}
                </h2>

                <form action="{{ route('profile.updatePassword') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">{{ __('app.current_password') }}</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.new_password') }}</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('app.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent">
                        🔑 {{ __('app.change_password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
