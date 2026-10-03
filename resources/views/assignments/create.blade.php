@extends('layouts.app')

@section('title', __('app.create_assignment'))

@section('content')
<div style="max-width: 780px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800;">{{ __('app.create_assignment') }}</h1>
            <p style="color: var(--text-muted); font-size: 14px;">{{ $classroom->name }} ({{ $classroom->code }})</p>
        </div>
        <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-sm">
            &larr; {{ __('app.cancel') }}
        </a>
    </div>

    <div class="card">
        <form action="{{ route('assignments.store', $classroom) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('app.assignment_title') }} *</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. التكليف الأول: بناء صفحة ويب متجاوبة" value="{{ old('title') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.description') }} *</label>
                <textarea name="description" class="form-control" rows="5" required placeholder="اكتب متطلبات وشروط التكليف بالتفصيل للطلاب...">{{ old('description') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">{{ __('app.due_date') }}</label>
                    <input type="datetime-local" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.max_points') }} *</label>
                    <input type="number" name="max_points" class="form-control" required min="1" max="1000" value="{{ old('max_points', 100) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.reference_file') }} (PDF, DOCX, ZIP - Max 20MB)</label>
                <input type="file" name="assignment_file" class="form-control">
                <small style="color: var(--text-muted); font-size: 12px;">ورقة الأسئلة أو ملف البيانات المرجعي للطلاب (اختياري).</small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="{{ route('classrooms.show', $classroom) }}" class="btn">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    ➕ {{ __('app.create_assignment') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
