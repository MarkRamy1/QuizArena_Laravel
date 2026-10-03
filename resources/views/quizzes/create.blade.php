@extends('layouts.app')

@section('title', __('app.create_quiz'))

@section('content')
<div style="max-width: 950px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800;">➕ {{ __('app.create_quiz') }}</h1>
            <p style="color: var(--text-muted); font-size: 14px;">{{ __('app.app_tagline') }}</p>
        </div>
        <a href="{{ route('quizzes.index') }}" class="btn btn-sm">
            &larr; {{ __('app.cancel') }}
        </a>
    </div>

    <form action="{{ route('quizzes.store') }}" method="POST" id="quizForm" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: Basic Information -->
        <div class="card" style="margin-bottom: 24px;">
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> 1. {{ __('app.quiz_title') }} & {{ __('app.settings') }}
            </h3>

            <div class="form-group">
                <label class="form-label">{{ __('app.quiz_title') }} *</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. اختبار الشهر الأول في البرمجة وتطوير الويب" value="{{ old('title') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('app.description') }}</label>
                <textarea name="description" class="form-control" rows="2" placeholder="وصف محتوى وأهداف الاختبار...">{{ old('description') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">{{ __('app.classrooms') }} (اختياري)</label>
                    <select name="classroom_id" class="form-control">
                        <option value="">عام لجميع الطلاب</option>
                        @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ (isset($classroomId) && $classroomId == $cls->id) ? 'selected' : '' }}>
                            {{ $cls->name }} ({{ $cls->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">المدة الإجمالية للاختبار (بالدقائق) *</label>
                    <input type="number" name="duration_minutes" class="form-control" min="1" max="300" required value="{{ old('duration_minutes', 20) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.passing_score') }} (%) *</label>
                    <input type="number" name="passing_score" class="form-control" min="0" max="100" required value="{{ old('passing_score', 60) }}">
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--surface-border); margin: 20px 0;">

            <!-- Advanced Proctoring & Timing Controls -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <!-- Timing Option: Per-Question Timer -->
                <div style="background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 800; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="has_per_question_timer" id="perQuestionCheckbox" value="1" onchange="togglePerQuestionInput(this)" style="width: auto;">
                            <span>⏱️ توقيت محدد لكل سؤال</span>
                        </label>
                        <span class="role-pill" style="font-size: 11px;">متقدم</span>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                        تفعيل عداد تنازلي خاص لكل سؤال ينتقل تلقائياً للسؤال التالي عند انتهاء وقته.
                    </p>
                    <div id="perQuestionBox" style="display: none;">
                        <label class="form-label" style="font-size: 12px;">الوقت الافتراضي لكل سؤال (بالثواني):</label>
                        <input type="number" name="per_question_seconds" class="form-control" min="5" max="600" value="45" placeholder="مثال: 45 أو 60 ثانية">
                    </div>
                </div>

                <!-- AI Proctoring Anti-Cheating -->
                <div style="background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--surface-border);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 800; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="is_ai_proctored" value="1" checked style="width: auto;">
                            <span>🛡️ مراقبة ذكية بالذكاء الاصطناعي (AI Proctor)</span>
                        </label>
                        <span class="role-pill" style="font-size: 11px; background: rgba(16,185,129,0.2); color: var(--success);">حارس النزاهة</span>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0;">
                        يراقب سلوك الطالب أثناء الامتحان: منع تبديل التبويبات، رصد مغادرة الشاشة، منع النسخ واللصق، وكاميرا التعرف وتوجيه إنذارات فورية.
                    </p>
                </div>
            </div>

            <!-- PDF Exam Sheet Upload -->
            <div style="margin-top: 16px; background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px dashed var(--accent);">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: var(--accent); margin-bottom: 6px;">
                    <span>📄</span> إرفاق ورقة الامتحان بصيغة PDF (اختياري)
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                    يمكنك إرفاق ملف PDF كامل للاختبار ليستطيع الطلاب تحميله أو حله وطباعته بجانب الإجابة الإلكترونية.
                </p>
                <input type="file" name="pdf_exam" class="form-control" accept="application/pdf">
            </div>

        </div>

        <!-- Section 2: Question Builder Supporting all 5 Question Types -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; border-bottom: 1px solid var(--surface-border); padding-bottom: 14px;">
                <h3 style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>❓</span> 2. {{ __('app.questions') }}
                </h3>

                <!-- Buttons for ALL 5 Question Types -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-primary" onclick="addQuestion('CHOOSE')">
                        ➕ {{ __('app.q_choose') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-accent" onclick="addQuestion('TRUE_FALSE')">
                        ➕ {{ __('app.q_true_false') }}
                    </button>
                    <button type="button" class="btn btn-sm" onclick="addQuestion('COMPLETE')">
                        ➕ {{ __('app.q_complete') }}
                    </button>
                    <button type="button" class="btn btn-sm" onclick="addQuestion('MATCH')">
                        ➕ {{ __('app.q_match') }}
                    </button>
                    <button type="button" class="btn btn-sm" onclick="addQuestion('ESSAY')">
                        ➕ {{ __('app.q_essay') }}
                    </button>
                </div>
            </div>

            <!-- Questions Container -->
            <div id="questionsContainer" style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Initial Choose Question -->
                <div class="q-block" data-idx="0" style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                        <span style="font-weight: 800; color: var(--primary);">السؤال 1: {{ __('app.q_choose') }}</span>
                        <input type="hidden" name="questions[0][type]" value="CHOOSE">
                        
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <label style="font-size: 12px; margin: 0;">⏱️ الثواني:</label>
                                <input type="number" name="questions[0][time_limit_seconds]" value="45" min="0" max="600" style="width: 70px; padding: 4px 8px;" class="form-control" title="الحد الزمني لهذا السؤال بالثواني (0 = بدون حد خاص)">
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <label style="font-size: 12px; margin: 0;">{{ __('app.points') }}:</label>
                                <input type="number" name="questions[0][points]" value="1" min="1" style="width: 70px; padding: 4px 8px;" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" name="questions[0][question_text]" class="form-control" required placeholder="اكتب نص السؤال هنا...">
                    </div>

                    <div style="margin: 12px 0;">
                        <label style="font-size: 12.5px; font-weight: 700; margin-bottom: 8px; display: block;">{{ __('app.options') }} (حدد الدائرة بجوار الإجابة الصحيحة):</label>
                        <div style="display: grid; gap: 8px;">
                            @for($i = 0; $i < 4; $i++)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="questions[0][correct_option]" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }} style="width: auto; cursor: pointer;">
                                <input type="text" name="questions[0][options][{{ $i }}]" class="form-control" placeholder="الخيار {{ $i + 1 }}" {{ $i < 2 ? 'required' : '' }}>
                            </div>
                            @endfor
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <input type="text" name="questions[0][explanation]" class="form-control" placeholder="توضيح أو تعليل الإجابة النموذجية (اختياري)...">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 17px;">
            🚀 نشر الاختبار
        </button>
    </form>

</div>

<script>
let qIndex = 1;

function togglePerQuestionInput(cb) {
    document.getElementById('perQuestionBox').style.display = cb.checked ? 'block' : 'none';
}

function addQuestion(type) {
    const container = document.getElementById('questionsContainer');
    const idx = qIndex++;

    const div = document.createElement('div');
    div.className = 'q-block';
    div.setAttribute('data-idx', idx);
    div.style.cssText = 'background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px;';

    let typeTitle = '';
    let bodyHtml = '';

    if (type === 'CHOOSE') {
        typeTitle = '{{ __("app.q_choose") }}';
        bodyHtml = `
            <div style="margin: 12px 0;">
                <label style="font-size: 12.5px; font-weight: 700; margin-bottom: 8px; display: block;">{{ __("app.options") }} (حدد الدائرة بجوار الإجابة الصحيحة):</label>
                <div style="display: grid; gap: 8px;">
                    ${[0, 1, 2, 3].map(i => `
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="questions[${idx}][correct_option]" value="${i}" ${i === 0 ? 'checked' : ''} style="width: auto; cursor: pointer;">
                            <input type="text" name="questions[${idx}][options][${i}]" class="form-control" placeholder="الخيار ${i + 1}" ${i < 2 ? 'required' : ''}>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    } else if (type === 'TRUE_FALSE') {
        typeTitle = '{{ __("app.q_true_false") }}';
        bodyHtml = `
            <div style="margin: 12px 0;">
                <label style="font-size: 12.5px; font-weight: 700; margin-bottom: 8px; display: block;">اختر الإجابة الصحيحة:</label>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 700;">
                        <input type="radio" name="questions[${idx}][correct_option]" value="0" checked style="width: auto;">
                        <span>صح (True)</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 700;">
                        <input type="radio" name="questions[${idx}][correct_option]" value="1" style="width: auto;">
                        <span>خطأ (False)</span>
                    </label>
                </div>
                <input type="hidden" name="questions[${idx}][options][0]" value="صح (True)">
                <input type="hidden" name="questions[${idx}][options][1]" value="خطأ (False)">
            </div>
        `;
    } else if (type === 'COMPLETE') {
        typeTitle = '{{ __("app.q_complete") }}';
        bodyHtml = `
            <div class="form-group" style="margin: 12px 0;">
                <label class="form-label">{{ __("app.model_answer") }} (الكلمة أو القيمة المطلوبة لملء الفراغ): *</label>
                <input type="text" name="questions[${idx}][model_answer]" class="form-control" required placeholder="مثال: HTTPS أو دالة الجمع...">
            </div>
        `;
    } else if (type === 'MATCH') {
        typeTitle = '{{ __("app.q_match") }}';
        bodyHtml = `
            <div style="margin: 12px 0;">
                <label class="form-label">{{ __("app.match_pairs_desc") }}</label>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    ${[0, 1, 2, 3].map(p => `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="text" name="questions[${idx}][match_pairs][${p}][left]" class="form-control" placeholder="{{ __('app.left_item') }} ${p + 1}" required>
                            <input type="text" name="questions[${idx}][match_pairs][${p}][right]" class="form-control" placeholder="{{ __('app.right_item') }} ${p + 1}" required>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    } else if (type === 'ESSAY') {
        typeTitle = '{{ __("app.q_essay") }}';
        bodyHtml = `
            <div class="form-group" style="margin: 12px 0;">
                <label class="form-label">{{ __("app.model_answer") }} (معايير التصحيح أو الإجابة المتوقعة):</label>
                <textarea name="questions[${idx}][model_answer]" class="form-control" rows="3" placeholder="الإجابة النموذجية الاسترشادية للتصحيح..."></textarea>
            </div>
        `;
    }

    div.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <span style="font-weight: 800; color: var(--primary);">السؤال ${idx + 1}: ${typeTitle}</span>
            <input type="hidden" name="questions[${idx}][type]" value="${type}">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; margin: 0;">⏱️ الثواني:</label>
                    <input type="number" name="questions[${idx}][time_limit_seconds]" value="45" min="0" max="600" style="width: 70px; padding: 4px 8px;" class="form-control" title="الحد الزمني لهذا السؤال بالثواني">
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; margin: 0;">{{ __("app.points") }}:</label>
                    <input type="number" name="questions[${idx}][points]" value="1" min="1" style="width: 70px; padding: 4px 8px;" class="form-control">
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.q-block').remove()">🗑️</button>
            </div>
        </div>

        <div class="form-group">
            <input type="text" name="questions[${idx}][question_text]" class="form-control" required placeholder="اكتب نص السؤال هنا...">
        </div>

        ${bodyHtml}

        <div class="form-group" style="margin-top: 10px;">
            <input type="text" name="questions[${idx}][explanation]" class="form-control" placeholder="توضيح أو تعليل الإجابة النموذجية (اختياري)...">
        </div>
    `;

    container.appendChild(div);
}
</script>
@endsection
