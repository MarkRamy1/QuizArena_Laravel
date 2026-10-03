@extends('layouts.app')

@section('title', __('app.my_classrooms'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 26px;">

    <!-- Header Section -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800;">{{ __('app.my_classrooms') }}</h1>
            <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.app_tagline') }}</p>
        </div>

        @if(Auth::user()->isTeacher())
        <a href="{{ route('classrooms.create') }}" class="btn btn-primary">
            ➕ {{ __('app.create_classroom') }}
        </a>
        @endif
    </div>

    @if(Auth::user()->isStudent())
    <!-- Student Actions: Join by Code & Search Teacher/Class -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Card 1: Join via Unique Code -->
        <div class="card" style="border-top: 4px solid var(--primary);">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <span>🔑</span> {{ __('app.join_by_code') }}
            </h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 14px;">
                {{ __('app.enter_code') }}
            </p>
            <form action="{{ route('classrooms.join') }}" method="POST" style="display: flex; gap: 10px;">
                @csrf
                <input type="text" name="code" class="form-control" placeholder="e.g. CS-101" required style="text-transform: uppercase;">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    {{ __('app.join_classroom') }}
                </button>
            </form>
        </div>

        <!-- Card 2: Search Teacher or Classroom -->
        <div class="card" style="border-top: 4px solid var(--accent);">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <span>🔍</span> {{ __('app.search_classrooms') }}
            </h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 14px;">
                {{ __('app.search_placeholder') }}
            </p>
            <form action="{{ route('classrooms.index') }}" method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="{{ __('app.search_placeholder') }}" required>
                <button type="submit" class="btn btn-accent" style="white-space: nowrap;">
                    {{ __('app.search') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Search Results if active -->
    @if(isset($search) && $search)
    <div class="card">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">
            🔍 {{ __('app.search') }}: "{{ $search }}" ({{ $searchResults->count() }})
        </h3>

        @if($searchResults->isEmpty())
            <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.no_records') }}</p>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @foreach($searchResults as $res)
                @php
                    $isMember = Auth::user()->enrolledClassrooms->contains($res->id);
                    $isPending = Auth::user()->enrolledClassrooms()->where('classrooms.id', $res->id)->wherePivot('status', 'PENDING')->exists();
                @endphp
                <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 800; padding: 2px 8px; border-radius: 999px; background: rgba(79,70,229,0.15); color: var(--primary);">
                                {{ $res->code }}
                            </span>
                            <span style="font-size: 12px; color: var(--text-muted);">👥 {{ $res->students_count }}</span>
                        </div>
                        <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 4px;">{{ $res->name }}</h4>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">👨‍🏫 {{ $res->teacher->name }} &bull; {{ $res->subject }}</p>
                    </div>

                    <div style="margin-top: 14px;">
                        @if($isMember)
                            <a href="{{ route('classrooms.show', $res) }}" class="btn btn-sm btn-primary" style="width: 100%;">
                                {{ __('app.view') }} ({{ __('app.request_accepted') }})
                            </a>
                        @elseif($isPending)
                            <button class="btn btn-sm" disabled style="width: 100%; opacity: 0.7;">
                                ⏳ {{ __('app.request_pending') }}
                            </button>
                        @else
                            <form action="{{ route('classrooms.request', $res) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-accent" style="width: 100%;">
                                    📩 {{ __('app.request_to_join') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    <!-- Pending Requests List (Student side) -->
    @if(isset($pendingClassrooms) && $pendingClassrooms->isNotEmpty())
    <div class="card" style="border-inline-start: 4px solid var(--warning);">
        <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <span>⏳</span> {{ __('app.pending_requests') }}
        </h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($pendingClassrooms as $pClass)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--surface); border-radius: var(--radius-sm);">
                <div>
                    <strong>{{ $pClass->name }}</strong> ({{ $pClass->code }}) — 👨‍🏫 {{ $pClass->teacher->name }}
                </div>
                <span class="role-pill" style="color: var(--warning); border-color: var(--warning); background: rgba(245, 158, 11, 0.1);">
                    {{ __('app.request_pending') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif

    <!-- Classrooms Grid List -->
    <div>
        <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 18px;">
            @if(Auth::user()->isTeacher())
                {{ __('app.my_classrooms') }} ({{ $classrooms->count() }})
            @else
                {{ __('app.my_classrooms') }} ({{ $enrolledClassrooms->count() }})
            @endif
        </h2>

        @php
            $displayClasses = Auth::user()->isTeacher() ? $classrooms : $enrolledClassrooms;
        @endphp

        @if($displayClasses->isEmpty())
        <div class="card" style="text-align: center; padding: 48px;">
            <div style="font-size: 48px; margin-bottom: 12px;">🏫</div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">{{ __('app.no_records') }}</h3>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 18px;">
                @if(Auth::user()->isTeacher())
                    ابدأ بإنشاء فصلك الدراسي الأول لمشاركة المواد والاختبارات مع الطلاب.
                @else
                    انضم إلى فصولك الدراسية عبر الكود أو بالبحث عن اسم المعلم.
                @endif
            </p>
            @if(Auth::user()->isTeacher())
            <a href="{{ route('classrooms.create') }}" class="btn btn-primary">➕ {{ __('app.create_classroom') }}</a>
            @endif
        </div>
        @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            @foreach($displayClasses as $class)
            <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; border-top: 5px solid {{ $class->theme_color ?? 'var(--primary)' }};">
                <div style="padding: 22px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 999px; background: rgba(79, 70, 229, 0.12); color: var(--primary);">
                            {{ $class->code }}
                        </span>

                        <button type="button" class="btn btn-sm" onclick="navigator.clipboard.writeText('{{ $class->code }}'); alert('{{ __('app.code_copied') }}');" title="{{ __('app.copy_code') }}" style="padding: 4px 8px; font-size: 11px;">
                            📋 {{ __('app.copy_code') }}
                        </button>
                    </div>

                    <h3 style="font-size: 19px; font-weight: 800; margin-bottom: 6px;">
                        <a href="{{ route('classrooms.show', $class) }}">{{ $class->name }}</a>
                    </h3>

                    <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 12px;">
                        📚 {{ $class->subject }} @if($class->grade) &bull; {{ $class->grade }} @endif
                    </p>

                    @if(!Auth::user()->isTeacher())
                    <p style="font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                        👨‍🏫 {{ __('app.teacher') }}: <strong>{{ $class->teacher->name }}</strong>
                    </p>
                    @endif

                    <div style="display: flex; gap: 14px; margin-top: 14px; font-size: 13px; color: var(--text-muted);">
                        <span>📝 {{ $class->assignments_count ?? 0 }} {{ __('app.assignments') }}</span>
                        <span>⏱️ {{ $class->quizzes_count ?? 0 }} {{ __('app.quizzes') }}</span>
                        @if(Auth::user()->isTeacher())
                        <span>👥 {{ $class->students_count ?? 0 }} {{ __('app.students') }}</span>
                        @endif
                    </div>
                </div>

                <div style="padding: 14px 22px; background: var(--surface); border-top: 1px solid var(--surface-border); display: flex; align-items: center; justify-content: space-between;">
                    <a href="{{ route('classrooms.show', $class) }}" class="btn btn-primary btn-sm" style="width: 100%;">
                        {{ __('app.view') }} &rarr;
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
