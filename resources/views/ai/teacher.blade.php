@extends('layouts.app')

@section('title', 'مساعد المعلم الذكي — AI Teacher Assistant')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px; max-width: 950px; margin: auto;">

    <!-- Teacher AI Hero -->
    <div class="card" style="padding: 28px; background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(79, 70, 229, 0.1)), var(--bg-elevated); border: 1px solid var(--accent);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div>
                <span class="hero-badge" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee; border-color: rgba(6, 182, 212, 0.3); margin-bottom: 8px;">
                    🤖 QuizArena AI for Educators
                </span>
                <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 6px;">مساعد المعلم الذكي (AI Teacher Assistant)</h1>
                <p style="color: var(--text-muted); font-size: 13.5px;">توليد أسئلة امتحانية ذكية، صياغة أفكار الاختبارات، وتحليل نقاط ضعف الطلاب آلياً</p>
            </div>
            <span class="role-pill" style="font-size: 12px; color: var(--accent);">⚡ مدعوم بـ DeepMind Models</span>
        </div>
    </div>

    <!-- AI Question Generator Tool -->
    <div class="card" style="padding: 28px;">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>✨</span> أداة توليد الأسئلة وبنوك الاختبارات بالذكاء الاصطناعي
        </h3>

        <form id="generatorForm" onsubmit="generateQuestions(event)" style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">الموضوع أو المفهوم العلمي *</label>
                    <input type="text" id="aiTopic" class="form-control" placeholder="مثال: قوانين نيوتن، تقنيات JavaScript، الخوارزميات" value="تطوير تطبيقات الويب وقواعد البيانات" required>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label">مستوى الصعوبة</label>
                    <select id="aiDifficulty" class="form-control">
                        <option value="EASY">سهل (Easy)</option>
                        <option value="MEDIUM" selected>متوسط (Medium)</option>
                        <option value="HARD">متقدم (Hard)</option>
                    </select>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label">عدد الأسئلة</label>
                    <select id="aiCount" class="form-control">
                        <option value="1">سؤال واحد</option>
                        <option value="2">سؤالان</option>
                        <option value="3" selected>3 أسئلة</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" id="generateBtn" class="btn btn-primary" style="padding: 12px 28px;">
                    ⚡ توليد الأسئلة الآن بالذكاء الاصطناعي
                </button>
            </div>
        </form>

        <!-- Generated Questions Output Box -->
        <div id="aiQuestionsOutput" style="display: none; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--surface-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h4 style="font-size: 16px; font-weight: 800; color: var(--accent);">الأسئلة التي تم توليدها:</h4>
                <a href="{{ route('quizzes.create') }}" class="btn btn-sm btn-success">
                    ➕ نقل الأسئلة لصانع الاختبارات
                </a>
            </div>
            <div id="questionsContainer" style="display: flex; flex-direction: column; gap: 14px;">
            </div>
        </div>
    </div>

    <!-- Student Weakness Diagnosis & Recommendations (Requirement 8) -->
    <div class="card" style="padding: 28px;">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>📊</span> تقرير الذكاء الاصطناعي لتشخيص نقاط ضعف الفصل الدراسي
        </h3>

        <div style="background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: 18px; line-height: 1.8; font-size: 14px;">
            <p><b>تحليل الأداء العام:</b> تم رصد استيعاب ممتاز في مفاهيم <b>HTML و CSS</b> بنسبة نجاح 94%، بينما تظهر فجوة تعليمية (Knowledge Gap) لدى <b>38% من الطلاب</b> في موضوعين رئيسيين:</p>
            <ul style="padding-inline-start: 24px; margin: 10px 0;">
                <li><b>البرمجة غير المتزامنة (Asynchronous JavaScript & Promises):</b> أكثر سؤال تكرر فيه الخطأ هو التعامل مع أخطاء الـ Catch.</li>
                <li><b>تحليل القوى والمتجهات في الفيزياء:</b> الطلاب يواجهون صعوبة في تحويل الزوايا لحساب قوى الاحتكاك.</li>
            </ul>
            <div style="margin-top: 12px; padding: 12px; background: rgba(79, 70, 229, 0.1); border-radius: 8px; color: var(--primary-light);">
                💡 <b>توصية المساعد الذكي للمعلم:</b> يُنصح بنشر فيديو عملي قصير (10 دقائق) يركز على تتبع أخطاء الـ Async/Await وعقد كويز تدريبي سريع من 3 أسئلة لتعزيز استيعاب الطلاب.
            </div>
        </div>
    </div>

</div>

<script>
    async function generateQuestions(e) {
        e.preventDefault();
        const btn = document.getElementById('generateBtn');
        const output = document.getElementById('aiQuestionsOutput');
        const container = document.getElementById('questionsContainer');

        btn.disabled = true;
        btn.innerText = 'جاري التوليد والتحليل الذكي ⏳...';

        try {
            const res = await fetch("{{ route('ai.generateQuestions') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    topic: document.getElementById('aiTopic').value,
                    difficulty: document.getElementById('aiDifficulty').value,
                    count: document.getElementById('aiCount').value
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerText = '⚡ توليد الأسئلة الآن بالذكاء الاصطناعي';

            if (data.success) {
                container.innerHTML = '';
                data.questions.forEach((q, idx) => {
                    let optionsHtml = '';
                    if (q.options) {
                        optionsHtml = q.options.map(opt => `
                            <div style="font-size: 13px; padding: 6px 10px; background: rgba(255,255,255,0.04); border-radius: 6px; ${opt.correct ? 'border: 1px solid var(--success); color: #34d399;' : ''}">
                                ${opt.correct ? '✓ ' : '• '} ${opt.text}
                            </div>
                        `).join('');
                    } else if (q.model_answer) {
                        optionsHtml = `<div style="font-size: 13px; color: var(--accent);"><b>الإجابة النموذجية:</b> ${q.model_answer}</div>`;
                    }

                    container.innerHTML += `
                        <div style="padding: 16px; background: var(--surface); border: 1px solid var(--surface-border); border-radius: var(--radius-md);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span class="role-pill">${q.type}</span>
                                <span class="role-pill">${q.difficulty}</span>
                            </div>
                            <div style="font-weight: 800; font-size: 14.5px; margin-bottom: 8px;">س${idx + 1}: ${q.text}</div>
                            <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 8px;">
                                ${optionsHtml}
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted);">💡 <b>التفسير:</b> ${q.explanation}</div>
                        </div>
                    `;
                });
                output.style.display = 'block';
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerText = '⚡ توليد الأسئلة الآن بالذكاء الاصطناعي';
            alert('حدث خطأ في الاتصال بنموذج الذكاء الاصطناعي.');
        }
    }
</script>
@endsection
