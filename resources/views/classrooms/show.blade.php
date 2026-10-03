@extends('layouts.app')

@section('title', $classroom->name)

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Classroom Header Banner -->
    <div style="background: linear-gradient(135deg, {{ $classroom->theme_color ?? '#4f46e5' }}, rgba(15, 23, 42, 0.95)), var(--bg-elevated); border-radius: var(--radius-xl); padding: 32px; color: #fff; box-shadow: var(--shadow-md); position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); backdrop-filter: blur(8px); padding: 6px 14px; border-radius: 999px; margin-bottom: 12px; font-size: 13px;">
                    <span>📚 {{ $classroom->subject }}</span>
                    @if($classroom->grade) &bull; <span>{{ $classroom->grade }}</span> @endif
                </div>

                <h1 style="font-size: clamp(24px, 3.5vw, 36px); font-weight: 900; margin-bottom: 10px;">{{ $classroom->name }}</h1>
                @if($classroom->description)
                <p style="opacity: 0.9; font-size: 15px; max-width: 800px; line-height: 1.6;">{{ $classroom->description }}</p>
                @endif

                <div style="display: flex; align-items: center; gap: 14px; margin-top: 16px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ $classroom->teacher->avatar_url }}" alt="{{ $classroom->teacher->name }}" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid #fff;">
                        <span style="font-weight: 700; font-size: 14px;">👨‍🏫 {{ $classroom->teacher->name }}</span>
                    </div>

                    @if(!Auth::user()->isTeacher() && Auth::id() !== $classroom->teacher_id)
                    <a href="{{ route('chat.private', $classroom->teacher->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff;">
                        💬 {{ __('app.chat_with_teacher') }}
                    </a>
                    @endif
                </div>
            </div>

            <!-- Classroom Code Badge with Copy -->
            <div style="background: rgba(0,0,0,0.35); backdrop-filter: blur(10px); padding: 16px 20px; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.15); text-align: center;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; opacity: 0.8; letter-spacing: 0.5px; margin-bottom: 4px;">
                    {{ __('app.classroom_code') }}
                </div>
                <div style="font-size: 22px; font-weight: 900; letter-spacing: 1.5px; color: #fff; margin-bottom: 8px;">
                    {{ $classroom->code }}
                </div>
                <button type="button" class="btn btn-sm" onclick="navigator.clipboard.writeText('{{ $classroom->code }}'); alert('{{ __('app.code_copied') }}');" style="background: #fff; color: #0f172a; width: 100%; border: none;">
                    📋 {{ __('app.copy_code') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 10px; border-bottom: 1px solid var(--surface-border); padding-bottom: 8px; overflow-x: auto;">
        <button type="button" class="tab-btn active" onclick="switchTab('stream', this)">
            💬 {{ __('app.classroom_stream') }}
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('assignments', this)">
            📝 {{ __('app.classroom_assignments') }} ({{ $classroom->assignments->count() }})
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('materials', this)">
            📂 {{ __('app.classroom_materials') }} ({{ $classroom->materials->count() }})
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('quizzes', this)">
            ⏱️ {{ __('app.classroom_quizzes') }} ({{ $classroom->quizzes->count() }})
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('members', this)">
            👥 {{ __('app.classroom_members') }} ({{ $classroom->students->count() }})
            @if($isTeacher && $pendingMembers->isNotEmpty())
            <span style="background: var(--warning); color: #000; font-size: 10px; padding: 1px 6px; border-radius: 999px; font-weight: 800;">
                {{ $pendingMembers->count() }}
            </span>
            @endif
        </button>
    </div>

    <!-- TAB 1: Stream & Global Chat -->
    <div id="tab-stream" class="tab-content">
        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            <!-- Post Message Form -->
            <div class="card">
                <form action="{{ route('classroom.messages.store', $classroom) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="display: flex; gap: 14px;">
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                        <div style="flex: 1;">
                            <textarea name="message" class="form-control" rows="3" placeholder="{{ __('app.type_message') }}..." required style="margin-bottom: 12px;"></textarea>

                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px; color: var(--text-muted);">
                                    📎 <span>{{ __('app.attach_file') }}</span>
                                    <input type="file" name="attachment" style="display: none;" onchange="this.nextElementSibling.innerText = this.files[0]?.name || ''">
                                    <span style="color: var(--primary); font-weight: 600;"></span>
                                </label>

                                <button type="submit" class="btn btn-primary">
                                    🚀 {{ __('app.send') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Messages Stream -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @if($classroom->messages->isEmpty())
                <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <div style="font-size: 38px; margin-bottom: 8px;">💬</div>
                    <p>{{ __('app.no_messages_yet') }}</p>
                </div>
                @else
                @foreach($classroom->messages->sortByDesc('created_at') as $msg)
                <div class="card" style="padding: 18px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px;">
                        <img src="{{ $msg->user->avatar_url }}" alt="{{ $msg->user->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="font-size: 14.5px;">{{ $msg->user->name }}</strong>
                                <span class="role-pill" style="font-size: 10.5px;">
                                    @if($msg->user->id === $classroom->teacher_id)
                                        👨‍🏫 {{ __('app.role_teacher') }}
                                    @else
                                        🎓 {{ __('app.role_student') }}
                                    @endif
                                </span>
                                <span style="font-size: 12px; color: var(--text-muted); margin-inline-start: auto;">
                                    {{ $msg->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p style="margin-top: 6px; font-size: 14.5px; line-height: 1.6; white-space: pre-line;">{{ $msg->message }}</p>

                            @if($msg->attachment)
                            <div style="margin-top: 10px;">
                                <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                    📥 <span>{{ __('app.download_file') }}</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- TAB 2: Classwork / Assignments -->
    <div id="tab-assignments" class="tab-content" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h2 style="font-size: 20px; font-weight: 800;">{{ __('app.classroom_assignments') }}</h2>
            @if($isTeacher)
            <a href="{{ route('assignments.create', $classroom) }}" class="btn btn-primary btn-sm">
                ➕ {{ __('app.create_assignment') }}
            </a>
            @endif
        </div>

        @if($classroom->assignments->isEmpty())
        <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
            <div style="font-size: 38px; margin-bottom: 8px;">📝</div>
            <p>{{ __('app.no_records') }}</p>
        </div>
        @else
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @foreach($classroom->assignments as $assignment)
            @php
                $mySub = $studentSubmissions[$assignment->id] ?? null;
            @endphp
            <div class="card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 20px;">📝</span>
                        <h3 style="font-size: 17px; font-weight: 800;">
                            <a href="{{ route('assignments.show', $assignment) }}">{{ $assignment->title }}</a>
                        </h3>
                    </div>
                    <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 8px; max-width: 700px;">
                        {{ Str::limit($assignment->description, 130) }}
                    </p>
                    <div style="display: flex; gap: 16px; font-size: 12.5px; color: var(--text-muted);">
                        <span>📅 {{ __('app.due_date') }}: {{ $assignment->due_date ? $assignment->due_date->format('Y-m-d H:i') : 'مفتوح بدون موعد' }}</span>
                        <span>💯 {{ __('app.max_points') }}: {{ $assignment->max_points }}</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    @if($isTeacher)
                        <span class="role-pill">
                            👥 {{ $assignment->submissions_count }} {{ __('app.submitted') }}
                        </span>
                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-primary btn-sm">
                            {{ __('app.view_submissions', ['count' => $assignment->submissions_count]) }}
                        </a>
                    @else
                        @if($mySub)
                            @if($mySub->status === 'GRADED')
                            <span class="role-pill" style="color: var(--success); border-color: var(--success); background: rgba(16,185,129,0.1);">
                                🏅 {{ $mySub->score }} / {{ $assignment->max_points }}
                            </span>
                            @else
                            <span class="role-pill" style="color: var(--primary); border-color: var(--primary); background: rgba(79,70,229,0.1);">
                                ✅ {{ __('app.submitted') }}
                            </span>
                            @endif
                        @else
                            <span class="role-pill" style="color: var(--danger); border-color: var(--danger); background: rgba(239,68,68,0.1);">
                                ⏳ {{ __('app.missing') }}
                            </span>
                        @endif
                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-primary">
                            {{ __('app.view') }} &rarr;
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- TAB 3: Materials & Files -->
    <div id="tab-materials" class="tab-content" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h2 style="font-size: 20px; font-weight: 800;">{{ __('app.classroom_materials') }}</h2>
            @if($isTeacher)
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('uploadModal').style.display='flex'">
                ➕ {{ __('app.upload_material') }}
            </button>
            @endif
        </div>

        @if($classroom->materials->isEmpty())
        <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
            <div style="font-size: 38px; margin-bottom: 8px;">📂</div>
            <p>{{ __('app.no_records') }}</p>
        </div>
        @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            @foreach($classroom->materials as $mat)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-size: 28px;">
                            @if($mat->external_url) 🔗 @elseif($mat->file_type === 'pdf') 📄 @else 📁 @endif
                        </span>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: var(--text-muted);">
                            {{ $mat->file_type ?? 'LINK' }}
                        </span>
                    </div>

                    <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 6px;">{{ $mat->title }}</h4>
                    @if($mat->description)
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">{{ $mat->description }}</p>
                    @endif
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 14px; border-top: 1px solid var(--surface-border); padding-top: 12px;">
                    @if($mat->file_path)
                    <a href="{{ route('materials.download', $mat) }}" class="btn btn-sm btn-primary" style="flex: 1;">
                        📥 {{ __('app.download') }}
                    </a>
                    @elseif($mat->external_url)
                    <a href="{{ $mat->external_url }}" target="_blank" class="btn btn-sm btn-accent" style="flex: 1;">
                        🔗 {{ __('app.open_link') }}
                    </a>
                    @endif

                    @if($isTeacher)
                    <form action="{{ route('materials.destroy', $mat) }}" method="POST" onsubmit="return confirm('{{ __('app.delete') }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 6px 10px;">
                            🗑️
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- TAB 4: Quizzes -->
    <div id="tab-quizzes" class="tab-content" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h2 style="font-size: 20px; font-weight: 800;">{{ __('app.classroom_quizzes') }}</h2>
            @if($isTeacher)
            <a href="{{ route('quizzes.create', ['classroom_id' => $classroom->id]) }}" class="btn btn-primary btn-sm">
                ➕ {{ __('app.create_quiz') }}
            </a>
            @endif
        </div>

        @if($classroom->quizzes->isEmpty())
        <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
            <div style="font-size: 38px; margin-bottom: 8px;">⏱️</div>
            <p>{{ __('app.no_records') }}</p>
        </div>
        @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px;">
            @foreach($classroom->quizzes as $quiz)
            @php
                $myQuizAttempts = $studentAttempts[$quiz->id] ?? collect();
                $latestAttempt = $myQuizAttempts->first();
            @endphp
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-size: 12px; font-weight: 800; padding: 2px 8px; border-radius: 999px; background: rgba(79,70,229,0.15); color: var(--primary);">
                            {{ $quiz->code }}
                        </span>
                        <span style="font-size: 13px; color: var(--text-muted);">
                            ⏳ {{ $quiz->duration_minutes }} {{ __('app.duration_minutes') }}
                        </span>
                    </div>

                    <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">{{ $quiz->title }}</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                        {{ Str::limit($quiz->description, 100) }}
                    </p>

                    <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 14px;">
                        <span>❓ {{ $quiz->questions->count() }} {{ __('app.questions') }}</span> &bull;
                        <span>🎯 {{ __('app.passing_score') }}: {{ $quiz->passing_score }}%</span>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--surface-border); padding-top: 14px;">
                    @if($isTeacher)
                        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-primary btn-sm" style="width: 100%;">
                            {{ __('app.view_attempts') }} ({{ $quiz->attempts_count }})
                        </a>
                    @else
                        @if($latestAttempt)
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <span style="font-size: 13px; font-weight: 700;">{{ __('app.your_score') }}:</span>
                                <span class="role-pill" style="color: {{ $latestAttempt->percentage >= $quiz->passing_score ? 'var(--success)' : 'var(--danger)' }};">
                                    {{ $latestAttempt->percentage }}%
                                </span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('quizzes.results', $latestAttempt) }}" class="btn btn-sm" style="flex: 1;">
                                    {{ __('app.review_quiz') }}
                                </a>
                                <form action="{{ route('quizzes.start', $quiz) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                                        🔄 إعادة
                                    </button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('quizzes.start', $quiz) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                                    🚀 {{ __('app.start_quiz') }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- TAB 5: Members & Requests -->
    <div id="tab-members" class="tab-content" style="display: none;">
        <!-- Teacher Profile Card -->
        <div class="card" style="margin-bottom: 20px;">
            <div style="font-size: 13px; text-transform: uppercase; font-weight: 800; color: var(--text-muted); margin-bottom: 12px;">
                {{ __('app.teacher') }}
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <img src="{{ $classroom->teacher->avatar_url }}" alt="{{ $classroom->teacher->name }}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                    <div>
                        <h4 style="font-size: 17px; font-weight: 800;">{{ $classroom->teacher->name }}</h4>
                        <p style="font-size: 13px; color: var(--text-muted);">{{ $classroom->teacher->email }}</p>
                    </div>
                </div>

                @if(Auth::id() !== $classroom->teacher_id)
                <a href="{{ route('chat.private', $classroom->teacher->id) }}" class="btn btn-sm btn-primary">
                    💬 {{ __('app.chat_with_teacher') }}
                </a>
                @endif
            </div>
        </div>

        <!-- Pending Join Requests (For Teacher only) -->
        @if($isTeacher && $pendingMembers->isNotEmpty())
        <div class="card" style="border-inline-start: 4px solid var(--warning); margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 14px; color: var(--warning); display: flex; align-items: center; gap: 8px;">
                <span>⏳</span> {{ __('app.pending_requests') }} ({{ $pendingMembers->count() }})
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($pendingMembers as $pStudent)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--surface); border-radius: var(--radius-sm); border: 1px solid var(--surface-border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="{{ $pStudent->avatar_url }}" alt="{{ $pStudent->name }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <strong>{{ $pStudent->name }}</strong>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $pStudent->email }}</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <form action="{{ route('classrooms.handleRequest', [$classroom, $pStudent]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="accept">
                            <button type="submit" class="btn btn-sm btn-success">
                                ✅ {{ __('app.approve') }}
                            </button>
                        </form>
                        <form action="{{ route('classrooms.handleRequest', [$classroom, $pStudent]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-sm btn-danger">
                                ❌ {{ __('app.reject') }}
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Enrolled Students List -->
        <div class="card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 18px; font-weight: 800; margin: 0;">
                    👥 {{ __('app.students') }} ({{ $classroom->students->count() }})
                </h3>
                @if($isTeacher)
                <a href="{{ route('guardian.log') }}" class="btn btn-sm">
                    👨‍👧‍👦 عرض سجل تنبيهات أولياء الأمور
                </a>
                @endif
            </div>

            @if($classroom->students->isEmpty())
            <p style="color: var(--text-muted); font-size: 14px;">لا يوجد طلاب منضمين بعد.</p>
            @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px;">
                @foreach($classroom->students as $student)
                <div style="display: flex; flex-direction: column; justify-content: space-between; padding: 14px; background: var(--surface); border-radius: var(--radius-md); border: 1px solid var(--surface-border); gap: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <strong style="font-size: 14.5px;">{{ $student->name }}</strong>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $student->email }}</div>
                            </div>
                        </div>

                        @if($isTeacher)
                        <a href="{{ route('chat.private', $student->id) }}" class="btn btn-sm" title="{{ __('app.chat_with_student') }}">
                            💬
                        </a>
                        @endif
                    </div>

                    <!-- Guardian Details & Absence Notification Actions -->
                    <div style="background: var(--bg); padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); font-size: 12px;">
                        @if($student->guardian_name || $student->guardian_phone)
                        <div style="color: var(--accent); font-weight: 700; margin-bottom: 4px;">
                            👨‍👧‍👦 ولي الأمر: {{ $student->guardian_name ?? 'ولي الأمر' }} ({{ $student->guardian_relation ?? 'ولي الأمر' }})
                        </div>
                        <div style="color: var(--text-muted); font-size: 11px;">
                            {{ $student->guardian_phone ?? $student->guardian_email }}
                        </div>
                        @else
                        <div style="color: var(--text-muted); font-size: 11px;">
                            لم يسجل الطالب بيانات ولي أمره بعد.
                        </div>
                        @endif

                        @if($isTeacher)
                        <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                            <form action="{{ route('guardian.alert') }}" method="POST" style="display: inline;" onsubmit="return confirm('إرسال إنذار غياب عن الحصة لولي أمر الطالب ({{ $student->name }})؟');">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                                <input type="hidden" name="type" value="SESSION_MISSED">
                                <button type="submit" class="btn btn-sm" style="font-size: 11px; padding: 3px 8px; background: rgba(245,158,11,0.15); color: var(--warning); border-color: rgba(245,158,11,0.3);">
                                    ⚠️ غياب عن حصة
                                </button>
                            </form>

                            <form action="{{ route('guardian.alert') }}" method="POST" style="display: inline;" onsubmit="return confirm('إرسال تنبيه غياب عن اختبار لولي أمر الطالب ({{ $student->name }})؟');">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                                <input type="hidden" name="type" value="EXAM_MISSED">
                                <button type="submit" class="btn btn-sm" style="font-size: 11px; padding: 3px 8px; background: rgba(239,68,68,0.15); color: var(--danger); border-color: rgba(239,68,68,0.3);">
                                    🚨 غياب عن اختبار
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Upload Material Modal (for Teachers) -->
@if($isTeacher)
<div id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); z-index: 100; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="max-width: 550px; width: 100%;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h3 style="font-size: 18px; font-weight: 800;">📂 {{ __('app.upload_material') }}</h3>
            <button type="button" class="btn btn-sm" onclick="document.getElementById('uploadModal').style.display='none'">✕</button>
        </div>

        <form action="{{ route('materials.store', $classroom) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('app.material_title') }} *</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. مذكرة الأسبوع الأول - ملخص المحاضرة">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.description') }}</label>
                <textarea name="description" class="form-control" rows="2" placeholder="{{ __('app.description') }}..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.file') }} (PDF, DOCX, ZIP, MP4 - Max 50MB)</label>
                <input type="file" name="material_file" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.external_link') }}</label>
                <input type="url" name="external_url" class="form-control" placeholder="https://youtube.com/... or Google Drive link">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn" onclick="document.getElementById('uploadModal').style.display='none'">
                    {{ __('app.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    💾 {{ __('app.upload_material') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<style>
.tab-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-weight: 700;
    font-size: 14.5px;
    padding: 10px 18px;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.tab-btn:hover {
    color: var(--text-main);
    background: var(--surface);
}
.tab-btn.active {
    color: var(--primary);
    background: rgba(79, 70, 229, 0.12);
}
</style>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabId).style.display = 'block';
    btn.classList.add('active');
}
</script>
@endsection
