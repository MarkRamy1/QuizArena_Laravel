@extends('layouts.app')

@section('title', $assignment->title)

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Navigation & Details -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-sm">
                &larr; {{ $classroom->name }}
            </a>
            <span class="role-pill">{{ $classroom->code }}</span>
        </div>

        <div style="display: flex; gap: 10px;">
            @if($assignment->file_path)
            <a href="{{ route('assignments.downloadReference', $assignment) }}" class="btn btn-sm btn-accent">
                📥 {{ __('app.reference_file') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Assignment Main Overview Card -->
    <div class="card" style="border-top: 4px solid var(--primary);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 6px;">{{ $assignment->title }}</h1>
                <p style="color: var(--text-muted); font-size: 13.5px;">
                    👨‍🏫 {{ $assignment->teacher->name }} &bull; {{ $assignment->created_at->format('Y-m-d') }}
                </p>
            </div>

            <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                <div style="background: var(--surface); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('app.max_points') }}</div>
                    <div style="font-size: 20px; font-weight: 900; color: var(--primary);">{{ $assignment->max_points }}</div>
                </div>

                <div style="background: var(--surface); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border); text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('app.due_date') }}</div>
                    <div style="font-size: 14px; font-weight: 800; color: {{ $assignment->isPastDue() ? 'var(--danger)' : 'var(--text-main)' }};">
                        {{ $assignment->due_date ? $assignment->due_date->format('Y-m-d H:i') : 'مفتوح' }}
                    </div>
                </div>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--surface-border); margin: 16px 0;">

        <div style="font-size: 15px; line-height: 1.7; white-space: pre-line;">
            {{ $assignment->description }}
        </div>
    </div>

    @if(!$isTeacher)
    <!-- STUDENT SUBMISSION SECTION -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
        @if($mySubmission)
        <!-- Already Submitted Card -->
        <div class="card" style="border-inline-start: 4px solid {{ $mySubmission->status === 'GRADED' ? 'var(--success)' : 'var(--primary)' }};">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h3 style="font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>📁</span> {{ __('app.submitted_on') }}: {{ $mySubmission->submitted_at?->format('Y-m-d H:i') }}
                </h3>

                <span class="role-pill" style="font-size: 12px; color: {{ $mySubmission->status === 'GRADED' ? 'var(--success)' : 'var(--primary)' }};">
                    @if($mySubmission->status === 'GRADED')
                        🏅 {{ __('app.graded') }}
                    @elseif($mySubmission->status === 'LATE')
                        ⚠️ {{ __('app.late') }}
                    @else
                        ✅ {{ __('app.submitted') }}
                    @endif
                </span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--surface); padding: 14px; border-radius: var(--radius-md); margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📄</span>
                    <div>
                        <strong>{{ $mySubmission->file_name ?? 'submitted_file' }}</strong>
                        @if($mySubmission->notes)
                        <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">{{ $mySubmission->notes }}</div>
                        @endif
                    </div>
                </div>

                @if($mySubmission->file_path)
                <a href="{{ route('submissions.download', $mySubmission) }}" class="btn btn-sm btn-primary">
                    📥 {{ __('app.download_file') }}
                </a>
                @endif
            </div>

            @if($mySubmission->status === 'GRADED')
            <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <strong style="color: var(--success); font-size: 16px;">{{ __('app.grade_submission') }}</strong>
                    <span style="font-size: 20px; font-weight: 900; color: var(--success);">
                        {{ $mySubmission->score }} / {{ $assignment->max_points }}
                    </span>
                </div>
                @if($mySubmission->feedback)
                <p style="font-size: 14px; color: var(--text-main); margin-top: 6px;">
                    <strong>{{ __('app.teacher_feedback') }}:</strong> {{ $mySubmission->feedback }}
                </p>
                @endif
            </div>
            @endif
        </div>
        @endif

        <!-- Submit or Resubmit Form -->
        <div class="card">
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 14px;">
                {{ $mySubmission ? '🔄 إعادة تسليم الواجب' : '📤 ' . __('app.submit_assignment') }}
            </h3>

            <form action="{{ route('assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">{{ __('app.upload_submission') }} *</label>
                    <input type="file" name="submission_file" class="form-control" required>
                    <small style="color: var(--text-muted); font-size: 12px;">PDF, Word (DOCX), ZIP, PNG, JPG (Max 25MB)</small>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.student_notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات توضيحية للمعلم بخصوص الحل...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    🚀 {{ __('app.submit_assignment') }}
                </button>
            </form>
        </div>
    </div>
    @else
    <!-- TEACHER VIEW: Submissions List & Grading -->
    <div class="card">
        <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>👥</span> {{ __('app.view_submissions', ['count' => $submissions->count()]) }}
        </h2>

        @if($submissions->isEmpty())
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">📥</div>
            <p>{{ __('app.no_records') }}</p>
        </div>
        @else
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @foreach($submissions as $sub)
            <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ $sub->student->avatar_url }}" alt="{{ $sub->student->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <strong style="font-size: 15px;">{{ $sub->student->name }}</strong>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                {{ __('app.submitted_on') }}: {{ $sub->submitted_at?->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="role-pill" style="color: {{ $sub->status === 'GRADED' ? 'var(--success)' : 'var(--warning)' }};">
                            {{ $sub->status === 'GRADED' ? __('app.graded') . " ({$sub->score}/{$assignment->max_points})" : __('app.submitted') }}
                        </span>

                        @if($sub->file_path)
                        <a href="{{ route('submissions.download', $sub) }}" class="btn btn-sm btn-primary">
                            📥 {{ __('app.download_file') }}
                        </a>
                        @endif
                    </div>
                </div>

                @if($sub->notes)
                <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 14px; background: var(--bg); padding: 10px 14px; border-radius: var(--radius-sm);">
                    💬 <strong>{{ __('app.student') }}:</strong> {{ $sub->notes }}
                </p>
                @endif

                <!-- Inline Grading Form -->
                <form action="{{ route('submissions.grade', $sub) }}" method="POST" style="background: var(--bg); border: 1px solid var(--surface-border); border-radius: var(--radius-sm); padding: 14px; margin-top: 10px;">
                    @csrf
                    <div style="display: grid; grid-template-columns: 140px 1fr auto; gap: 12px; align-items: center;">
                        <div>
                            <label class="form-label" style="font-size: 12px;">{{ __('app.enter_grade', ['max' => $assignment->max_points]) }}</label>
                            <input type="number" name="score" class="form-control" min="0" max="{{ $assignment->max_points }}" value="{{ $sub->score ?? '' }}" required placeholder="0 - {{ $assignment->max_points }}">
                        </div>

                        <div>
                            <label class="form-label" style="font-size: 12px;">{{ __('app.teacher_feedback') }}</label>
                            <input type="text" name="feedback" class="form-control" value="{{ $sub->feedback ?? '' }}" placeholder="ملاحظات وتوجيهات للطالب...">
                        </div>

                        <div style="margin-top: 22px;">
                            <button type="submit" class="btn btn-sm btn-success">
                                💾 {{ __('app.grade_submission') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
