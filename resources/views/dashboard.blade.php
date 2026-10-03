@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 26px;">

    <!-- Welcome Hero Banner -->
    <div class="card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(6, 182, 212, 0.1)), var(--bg-elevated); border: 1px solid var(--surface-border); padding: 30px; position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="role-pill" style="margin-bottom: 8px; display: inline-block;">
                    @if($user->isTeacher()) {{ __('app.dash_teacher_tier') }} @else {{ __('app.dash_student_tier') }} @endif
                </span>
                <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 6px;">
                    {{ __('app.welcome_back') }}، {{ $user->name }} 👋
                </h1>
                <p style="color: var(--text-muted); font-size: 14.5px;">
                    {{ __('app.dash_welcome_sub') }}
                </p>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                @if($user->isTeacher())
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    {{ __('app.dash_create_course') }}
                </a>
                <a href="{{ route('quizzes.create') }}" class="btn btn-accent">
                    {{ __('app.dash_quiz_maker') }}
                </a>
                <a href="{{ route('ai.teacher') }}" class="btn">
                    {{ __('app.dash_teacher_ai') }}
                </a>
                @else
                <a href="{{ route('courses.index') }}" class="btn btn-primary">
                    {{ __('app.dash_browse_courses') }}
                </a>
                <a href="{{ route('quizzes.index') }}" class="btn btn-accent">
                    {{ __('app.dash_take_quiz') }}
                </a>
                <a href="{{ route('ai.tutor') }}" class="btn">
                    {{ __('app.dash_ai_tutor') }}
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(!$user->isTeacher())
    <!-- ==================== STUDENT SYSTEM ==================== -->

    <!-- Gamification & Streak Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div class="card" style="display: flex; align-items: center; gap: 16px; border-color: rgba(79, 70, 229, 0.3);">
            <div style="font-size: 34px; background: rgba(79, 70, 229, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                ⭐
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">{{ __('app.dash_academic_level') }}</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--primary-light);">{{ __('app.level') }} {{ $level }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">{{ $xp }} XP {{ __('app.dash_total_xp') }}</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 16px; border-color: rgba(245, 158, 11, 0.3);">
            <div style="font-size: 34px; background: rgba(245, 158, 11, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                🔥
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">{{ __('app.dash_streak_title') }}</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--warning);">{{ __('app.dash_streak_days', ['days' => $streak]) }}</div>
                <div style="font-size: 11px; color: var(--success);">{{ __('app.dash_streak_keep_up') }}</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 34px; background: rgba(16, 185, 129, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                🎯
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">{{ __('app.dash_avg_score') }}</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--success);">{{ $averageScore }}%</div>
                <div style="font-size: 11px; color: var(--text-muted);">{{ $completedQuizzesCount }} {{ __('app.dash_completed_quizzes') }}</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 34px; background: rgba(6, 182, 212, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📜
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">{{ __('app.dash_digital_certs') }}</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--accent);">{{ $certificatesCount }}</div>
                <a href="{{ route('certificates.index') }}" style="font-size: 11px; color: var(--primary-light); text-decoration: underline;">{{ __('app.dash_view_certs') }}</a>
            </div>
        </div>
    </div>

    <!-- Teacher Connection & Teacher Quizzes Alert (Requirement 3 & 5) -->
    @if($myTeacher)
    <div class="card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(6, 182, 212, 0.08)), var(--bg-elevated); border: 1px solid rgba(16, 185, 129, 0.3); padding: 18px 22px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    👨‍🏫
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">{{ __('app.dash_your_teacher') }}</div>
                    <div style="font-weight: 800; font-size: 16px; color: var(--text-main);">{{ $myTeacher->name }}</div>
                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ __('app.dash_teacher_code_label') }} <b style="color: var(--primary-light);">{{ $myTeacher->teacher_code }}</b></div>
                </div>
            </div>
            <a href="{{ route('chat.sendPrivate', $myTeacher->id) }}" class="btn btn-sm btn-primary">
                {{ __('app.dash_message_teacher') }}
            </a>
        </div>
    </div>
    @else
    <div class="card" style="border: 2px dashed var(--accent); background: rgba(6, 182, 212, 0.04); padding: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span style="font-size: 24px;">🔗</span>
            <h3 style="font-size: 17px; font-weight: 800;">{{ __('app.dash_link_teacher_title') }}</h3>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">
            {{ __('app.dash_link_teacher_desc') }}
        </p>
        <form action="{{ route('student.linkTeacher') }}" method="POST" style="display: flex; gap: 10px; max-width: 480px;">
            @csrf
            <input type="text"
                   name="teacher_code"
                   placeholder="مثال: TCH-XXXXX"
                   class="form-control"
                   style="font-size: 15px; font-weight: 800; text-transform: uppercase;"
                   required>
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                {{ __('app.dash_link_teacher_btn') }}
            </button>
        </form>
    </div>
    @endif

    <!-- New Tests from Teacher (Requirement 5: Test code sent to students immediately) -->
    @if($teacherQuizzes->isNotEmpty())
    <div class="card" style="border: 1px solid var(--primary); background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), transparent), var(--bg-elevated); padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 22px;">📢</span>
                <h3 style="font-size: 17px; font-weight: 800;">اختبارات جديدة منشورة من معلمك</h3>
            </div>
            <span class="role-pill" style="font-size: 11px;">متاحة الآن</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($teacherQuizzes as $tq)
            <div style="padding: 14px 18px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="font-weight: 800; font-size: 15px;">{{ $tq->title }}</div>
                    <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span>كود الاختبار: <b style="color: var(--primary-light); font-size: 14px; font-family: monospace;">{{ $tq->code }}</b></span>
                        <span>&bull; المدة: {{ $tq->duration_minutes }} دقيقة</span>
                        <span>&bull; النجاح من: {{ $tq->passing_score }}%</span>
                    </div>
                </div>
                <a href="{{ route('quizzes.show', $tq) }}" class="btn btn-sm btn-accent" style="padding: 8px 18px;">
                    بدء الاختبار الآن ⚡
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Join Quiz Card & Daily Missions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
        <!-- Join Quiz by Code Card -->
        <div class="card" style="border: 2px dashed var(--primary); background: rgba(79, 70, 229, 0.04);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <span style="font-size: 24px;">⚡</span>
                <h3 style="font-size: 18px; font-weight: 800;">دخول اختبار بكود أو رمز PIN</h3>
            </div>
            <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 16px;">
                أدخل الكود الذي شاركه معك المعلم لبدء الاختبار فوراً:
            </p>

            <form action="{{ route('landing.joinQuiz') }}" method="POST">
                @csrf
                <div style="display: flex; gap: 10px;">
                    <input type="text"
                           name="code"
                           placeholder="مثال: CS-QZ101 أو PIN"
                           class="form-control"
                           style="font-size: 15px; font-weight: 800; text-transform: uppercase;"
                           required>
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                        دخول 🚀
                    </button>
                </div>
            </form>
        </div>

        <!-- Daily Missions Card -->
        <div class="card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 22px;">🎯</span>
                    <h3 style="font-size: 17px; font-weight: 800;">المهام اليومية (Daily Missions)</h3>
                </div>
                <span class="role-pill" style="font-size: 10.5px;">تتجدد يومياً</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($dailyMissions as $m)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                    <div>
                        <div style="font-weight: 700; font-size: 13.5px;">{{ $m->title }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">+{{ $m->xp_reward }} XP مكافأة</div>
                    </div>
                    @if($m->user_progress >= $m->target_count)
                        <span class="btn btn-sm btn-success" style="padding: 4px 10px; font-size: 11.5px;">
                            ✓ مكتملة
                        </span>
                    @else
                        <span class="role-pill" style="font-size: 11px;">
                            قيد الإنجاز
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Personalized Learning & Weak Area Diagnosis (Requirement 10) -->
    <div class="card" style="border: 1px solid rgba(245, 158, 11, 0.4); background: linear-gradient(180deg, rgba(245, 158, 11, 0.05) 0%, transparent 100%), var(--bg-elevated);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 26px;">🎯</span>
                <div>
                    <h2 style="font-size: 19px; font-weight: 900;">مسار التعلم المخصص والتشخيص الذكي (Personalized Learning)</h2>
                    <p style="font-size: 13px; color: var(--text-muted);">يقوم النظام بتحليل نتائجك السابقة لتحديد نقاط القوة ومواطن الضعف التي تحتاج لتعزيز</p>
                </div>
            </div>
            <a href="{{ route('ai.tutor') }}" class="btn btn-sm btn-accent">
                🤖 استشارة المعلم الذكي
            </a>
        </div>

        <!-- Subject Scores Diagnosis Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); padding: 14px 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 800; font-size: 14.5px;">الفيزياء</span>
                    <span style="font-weight: 900; color: #f87171; font-size: 16px;">62% ⚠️</span>
                </div>
                <div style="font-size: 12px; color: #fca5a5; margin-top: 4px;">يحتاج تركيز ومراجعة (Weak Area)</div>
                <div style="font-size: 12px; margin-top: 8px; color: var(--text-main);">
                    <b>موصى به لك:</b> قوانين نيوتن وقوى الاحتكاك
                </div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 14px 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 800; font-size: 14.5px;">الرياضيات</span>
                    <span style="font-weight: 900; color: #34d399; font-size: 16px;">81% ✓</span>
                </div>
                <div style="font-size: 12px; color: #6ee7b7; margin-top: 4px;">مستوى جيد جداً (Strong Area)</div>
                <div style="font-size: 12px; margin-top: 8px; color: var(--text-main);">
                    <b>موصى به لك:</b> تدريبات متقدمة في المصفوفات
                </div>
            </div>

            <div style="background: rgba(79, 70, 229, 0.1); border: 1px solid rgba(79, 70, 229, 0.3); border-radius: var(--radius-md); padding: 14px 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 800; font-size: 14.5px;">علوم الحاسب</span>
                    <span style="font-weight: 900; color: #818cf8; font-size: 16px;">92% 🌟</span>
                </div>
                <div style="font-size: 12px; color: #a5b4fc; margin-top: 4px;">مستوى استثنائي (Mastery)</div>
                <div style="font-size: 12px; margin-top: 8px; color: var(--text-main);">
                    <b>موصى به لك:</b> خوض معارك 1v1 التنافسية
                </div>
            </div>
        </div>

        <!-- Recommended Courses Cards -->
        <div>
            <div style="font-weight: 800; font-size: 14px; margin-bottom: 12px; color: var(--text-main);">
                💡 دروس ومراجعات مقترحة لرفع مستواك (Recommended for You):
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                @foreach($recommendedCourses as $rc)
                <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 14px; display: flex; gap: 14px; align-items: center;">
                    <div style="font-size: 28px; background: rgba(79, 70, 229, 0.15); width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); flex-shrink: 0;">
                        📚
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 800; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $rc->title }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $rc->subject }} &bull; {{ $rc->lessons->count() }} دروس</div>
                    </div>
                    <a href="{{ route('courses.show', $rc->id) }}" class="btn btn-sm btn-primary">
                        ابدأ 🚀
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Active Courses & Available Quizzes -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
        <!-- Courses in Progress -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>📚</span> {{ __('app.dash_available_courses') }}
                </h3>
                <a href="{{ route('courses.index') }}" class="btn btn-sm">{{ __('app.dash_view_all') }}</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($courses as $c)
                @php $prog = $c->getProgressForUser($user); @endphp
                <div style="padding: 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-weight: 800; font-size: 14px;">{{ $c->title }}</span>
                        <span class="role-pill">{{ $c->subject }}</span>
                    </div>

                    <!-- Progress Bar -->
                    <div style="margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: var(--text-muted); margin-bottom: 4px;">
                            <span>{{ __('app.dash_completion_rate') }}</span>
                            <span>{{ $prog }}%</span>
                        </div>
                        <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $prog }}%; background: linear-gradient(90deg, var(--primary), var(--accent));"></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $c->lessons->count() }} {{ __('app.dash_lessons_count') }}</span>
                        <a href="{{ route('courses.show', $c->id) }}" class="btn btn-sm btn-primary">
                            {{ __('app.dash_continue_learning') }}
                        </a>
                    </div>
                </div>
                @empty
                <p style="text-align: center; color: var(--text-muted); padding: 20px;">{{ __('app.dash_no_courses_yet') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Available Quizzes -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> {{ __('app.dash_quizzes_ready') }}
                </h3>
                <a href="{{ route('quizzes.index') }}" class="btn btn-sm">{{ __('app.dash_quiz_arena') }}</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($availableQuizzes as $q)
                <div style="padding: 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 800; font-size: 14px;">{{ $q->title }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                            {{ __('app.dash_quiz_code_label') }} <b>{{ $q->code }}</b> &bull; {{ $q->duration_minutes }} {{ __('app.dash_minutes') }} &bull; {{ __('app.dash_passing_label') }} {{ $q->passing_score }}%
                        </div>
                    </div>
                    <a href="{{ route('quizzes.show', $q->id) }}" class="btn btn-sm btn-accent">
                        {{ __('app.dash_start_solving') }}
                    </a>
                </div>
                @empty
                <p style="text-align: center; color: var(--text-muted); padding: 20px;">{{ __('app.dash_no_quizzes_yet') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    @else
    <!-- ==================== TEACHER SYSTEM ==================== -->

    <!-- Teacher Shareable Code Card (Requirement: teacher shares code with students to track assignment) -->
    <div class="card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(6, 182, 212, 0.08)), var(--bg-elevated); border: 1px solid rgba(79, 70, 229, 0.35); padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 700; margin-bottom: 6px;">{{ __('app.dash_unique_teacher_code') }}</div>
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span id="teacherCodeSpan" style="font-size: 26px; font-weight: 900; letter-spacing: 2px; color: var(--accent); font-family: monospace; background: var(--surface); padding: 6px 18px; border-radius: var(--radius-sm); border: 1px dashed var(--accent);">
                        {{ $teacherCode }}
                    </span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="copyTeacherCode('{{ $teacherCode }}')">
                        {{ __('app.dash_copy_code') }}
                    </button>
                    <span id="copyFeedback" style="font-size: 13px; color: var(--success); font-weight: 700; display: none;">{{ __('app.dash_copied_success') }}</span>
                </div>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 10px; max-width: 680px; line-height: 1.6;">
                    {{ __('app.dash_teacher_code_notice') }}
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('quizzes.create') }}" class="btn btn-accent" style="padding: 10px 20px;">
                    {{ __('app.dash_quiz_maker') }}
                </a>
                <a href="{{ route('courses.create') }}" class="btn" style="padding: 10px 20px;">
                    {{ __('app.dash_create_course') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Assigned Students List (Admin & Teacher tracking) -->
    <div class="card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 22px;">👨‍🎓</span>
                <h3 style="font-size: 18px; font-weight: 800;">{{ __('app.dash_assigned_students') }} ({{ $assignedStudents->count() }})</h3>
            </div>
            <span class="role-pill" style="font-size: 11px;">{{ __('app.dash_link_code_label') }} {{ $teacherCode }}</span>
        </div>

        @if($assignedStudents->isNotEmpty())
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 10px 12px;">{{ __('app.dash_th_student_name') }}</th>
                        <th style="padding: 10px 12px;">{{ __('app.dash_th_email') }}</th>
                        <th style="padding: 10px 12px;">{{ __('app.dash_th_joined_date') }}</th>
                        <th style="padding: 10px 12px;">{{ __('app.dash_th_quizzes_taken') }}</th>
                        <th style="padding: 10px 12px;">{{ __('app.dash_th_xp') }}</th>
                        <th style="padding: 10px 12px;">{{ __('app.dash_th_action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedStudents as $student)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(79, 70, 229, 0.15); display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                👤
                            </div>
                            <span>{{ $student->name }}</span>
                        </td>
                        <td style="padding: 12px; color: var(--text-muted); font-family: monospace;">{{ $student->email }}</td>
                        <td style="padding: 12px; color: var(--text-muted); font-size: 12.5px;">{{ $student->created_at->format('Y-m-d') }}</td>
                        <td style="padding: 12px; font-weight: 800;">{{ $student->quiz_attempts_count }}</td>
                        <td style="padding: 12px; font-weight: 800; color: var(--warning);">⚡ {{ $student->xp }} XP</td>
                        <td style="padding: 12px;">
                            <a href="{{ route('chat.sendPrivate', $student->id) }}" class="btn btn-sm">
                                {{ __('app.dash_chat') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
            <div style="font-size: 42px; margin-bottom: 10px;">👥</div>
            <h4 style="font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">لم ينضم أي طالب باستخدام كودك حتى الآن</h4>
            <p style="font-size: 13.5px; max-width: 480px; margin: auto; line-height: 1.6;">
                شارك كود المعلم الخاص بك <b style="color: var(--accent); font-family: monospace;">{{ $teacherCode }}</b> مع طلابك ليقوموا بإدخاله عند التسجيل أو في لوحة تحكمهم ليظهروا هنا فوراً.
            </p>
        </div>
        @endif
    </div>

    <!-- Teacher KPIs Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card" style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 34px; background: rgba(79, 70, 229, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                👥
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">إجمالي الطلاب</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--primary);">{{ $totalStudents }}</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 34px; background: rgba(6, 182, 212, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📝
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">الاختبارات المنشورة</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--accent);">{{ $totalQuizzes }}</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 34px; background: rgba(16, 185, 129, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📈
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">متوسط درجات الطلاب</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--success);">{{ $classAverageScore }}%</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 34px; background: rgba(245, 158, 11, 0.12); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📚
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 700;">الدورات المنشورة</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--warning);">{{ $totalCourses }}</div>
            </div>
        </div>
    </div>

    <!-- Teacher Analytics: Hardest Questions & Students Needing Help -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <!-- Hardest Questions -->
        <div class="card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <span style="font-size: 22px;">⚠️</span>
                <h3 style="font-size: 17px; font-weight: 800;">أصعب الأسئلة (أعلى نسبة خطأ لدى الطلاب)</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($hardestQuestions as $hq)
                <div style="padding: 12px 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                    <div style="font-weight: 700; font-size: 13.5px; margin-bottom: 6px;">
                        {{ Str::limit($hq->question_text, 80) }}
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted);">
                        <span class="role-pill">{{ $hq->difficulty ?? 'MEDIUM' }}</span>
                        <span style="color: var(--danger); font-weight: 700;">تكرار الخطأ: {{ $hq->wrong_answers_count }} مرة</span>
                    </div>
                </div>
                @empty
                <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                    لا توجد أسئلة ذات نسبة خطأ مسجلة حتى الآن.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Students Needing Help -->
        <div class="card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <span style="font-size: 22px;">🆘</span>
                <h3 style="font-size: 17px; font-weight: 800;">الطلاب المحتاجون للمساعدة (أقل من 60%)</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($studentsNeedingHelp as $snh)
                <div style="padding: 12px 14px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 800; font-size: 14px;">{{ $snh->student->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            اختبار: {{ $snh->quiz->title }} &bull; النتيجة: <b style="color: var(--danger);">{{ $snh->percentage }}%</b>
                        </div>
                    </div>
                    <a href="{{ route('chat.sendPrivate', $snh->student->id) }}" class="btn btn-sm">
                        مراسلة 💬
                    </a>
                </div>
                @empty
                <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                    لا يوجد طلاب بحاجة لمساعدة إضافية حتى الآن.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Attempts with Anti-Cheating Violations Monitoring -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <span>🛡️</span> مراقبة النزاهة وسجل المحاولات الأخيرة
            </h3>
            <a href="{{ route('quizzes.analytics') }}" class="btn btn-sm">عرض كل التحليلات</a>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--surface-border); color: var(--text-muted); font-size: 12px;">
                        <th style="padding: 10px 12px;">الطالب</th>
                        <th style="padding: 10px 12px;">الاختبار</th>
                        <th style="padding: 10px 12px;">النتيجة</th>
                        <th style="padding: 10px 12px;">مؤشر النزاهة والغش</th>
                        <th style="padding: 10px 12px;">التوقيت</th>
                        <th style="padding: 10px 12px;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttempts as $att)
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <td style="padding: 12px; font-weight: 700;">{{ $att->student->name }}</td>
                        <td style="padding: 12px;">{{ $att->quiz->title }}</td>
                        <td style="padding: 12px; font-weight: 900; color: {{ $att->percentage >= 60 ? 'var(--success)' : 'var(--danger)' }};">
                            {{ $att->percentage }}%
                        </td>
                        <td style="padding: 12px;">
                            @if($att->proctor_violations_count > 0)
                            <span class="role-pill" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.3);">
                                ⚠️ {{ $att->proctor_violations_count }} تبديل نوافذ
                            </span>
                            @else
                            <span class="role-pill" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
                                ✓ نزاهة تامة
                            </span>
                            @endif
                        </td>
                        <td style="padding: 12px; color: var(--text-muted); font-size: 12px;">{{ $att->created_at->diffForHumans() }}</td>
                        <td style="padding: 12px;">
                            <a href="{{ route('quizzes.results', $att->id) }}" class="btn btn-sm">
                                التفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 24px; text-align: center; color: var(--text-muted);">
                            لا توجد محاولات اختبار مسجلة بعد.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script>
function copyTeacherCode(code) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(() => {
            showCopyFeedback();
        }).catch(() => fallbackCopy(code));
    } else {
        fallbackCopy(code);
    }
}

function fallbackCopy(text) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    showCopyFeedback();
}

function showCopyFeedback() {
    const feedback = document.getElementById('copyFeedback');
    if (feedback) {
        feedback.style.display = 'inline';
        setTimeout(() => { feedback.style.display = 'none'; }, 3000);
    }
}
</script>
@endsection
