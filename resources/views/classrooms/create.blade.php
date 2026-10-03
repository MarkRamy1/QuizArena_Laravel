@extends('layouts.app')

@section('title', __('app.create_classroom'))

@section('content')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800;">{{ __('app.create_classroom') }}</h1>
            <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.app_tagline') }}</p>
        </div>
        <a href="{{ route('classrooms.index') }}" class="btn btn-sm">
            &larr; {{ __('app.cancel') }}
        </a>
    </div>

    <div class="card">
        <form action="{{ route('classrooms.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('app.classroom_name') }} *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. علوم الحاسب والبرمجة الحديثة" required value="{{ old('name') }}">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">{{ __('app.subject') }} *</label>
                    <input type="text" name="subject" class="form-control" placeholder="e.g. علوم الحاسب / Computer Science" required value="{{ old('subject') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.grade_level') }}</label>
                    <input type="text" name="grade" class="form-control" placeholder="e.g. المستوى الأول / الصف الثاني عشر" value="{{ old('grade') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.description') }}</label>
                <textarea name="description" class="form-control" rows="4" placeholder="{{ __('app.description') }}...">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.theme_color') }}</label>
                <div style="display: flex; align-items: center; gap: 14px;">
                    <input type="color" name="theme_color" value="{{ old('theme_color', '#4f46e5') }}" style="width: 50px; height: 42px; padding: 2px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); cursor: pointer;">
                    <span style="font-size: 13px; color: var(--text-muted);">اختر لوناً مميزاً للفصل الدراسي للتمييز بين الفصول.</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="{{ route('classrooms.index') }}" class="btn">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    ➕ {{ __('app.create_classroom') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
