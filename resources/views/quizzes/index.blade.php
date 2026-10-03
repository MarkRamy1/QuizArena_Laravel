@extends('layouts.app')

@section('title', __('app.quizzes'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800;">{{ __('app.quizzes') }}</h1>
            <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.app_tagline') }}</p>
        </div>

        @if(Auth::user()->isTeacher())
        <a href="{{ route('quizzes.create') }}" class="btn btn-primary">
            ➕ {{ __('app.create_quiz') }}
        </a>
        @endif
    </div>

    <!-- Quizzes Grid -->
    @if($quizzes->isEmpty())
    <div class="card" style="text-align: center; padding: 48px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 12px;">⏱️</div>
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">{{ __('app.no_records') }}</h3>
        <p style="font-size: 14px;">لا توجد اختبارات متاحة حالياً.</p>
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        @foreach($quizzes as $quiz)
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 12px; font-weight: 800; padding: 3px 8px; border-radius: 999px; background: rgba(79,70,229,0.15); color: var(--primary);">
                        {{ $quiz->code }}
                    </span>
                    @if($quiz->classroom)
                    <span style="font-size: 12px; color: var(--text-muted);">
                        🏫 {{ $quiz->classroom->name }}
                    </span>
                    @else
                    <span style="font-size: 12px; color: var(--text-muted);">
                        🌐 عام
                    </span>
                    @endif
                </div>

                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">
                    <a href="{{ route('quizzes.show', $quiz) }}">{{ $quiz->title }}</a>
                </h3>

                <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
                    {{ Str::limit($quiz->description, 110) }}
                </p>

                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                    <div>⏱️ {{ __('app.duration_minutes') }}: <strong>{{ $quiz->duration_minutes }} دقيقة</strong></div>
                    <div>❓ {{ __('app.questions') }}: <strong>{{ $quiz->questions_count }}</strong></div>
                    <div>🎯 {{ __('app.passing_score') }}: <strong>{{ $quiz->passing_score }}%</strong></div>
                    @if($quiz->teacher)
                    <div>👨‍🏫 {{ __('app.teacher') }}: <strong>{{ $quiz->teacher->name }}</strong></div>
                    @endif
                </div>
            </div>

            <div style="border-top: 1px solid var(--surface-border); padding-top: 14px;">
                <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-primary" style="width: 100%;">
                    {{ __('app.view') }} &rarr;
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
