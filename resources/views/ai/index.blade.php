@extends('layouts.app')

@section('title', 'المعلم الذكي والمساعد الأكاديمي — AI Tutor')

@section('content')
<div style="max-width: 900px; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 8px;">
                <span>🤖</span> المعلم الذكي التفاعلي (AI Tutor)
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">مساعدك الشخصي لشرح الدروس وحل المسائل وفهم المفاهيم خطوة بخطوة</p>
        </div>
        <span class="role-pill" style="font-size: 12px; padding: 6px 14px;">
            🎓 المساعد الأكاديمي الفوري
        </span>
    </div>

    <!-- Active Exam Guard Warning Banner -->
    @if($activeAttempt)
    <div class="alert warning" style="margin-bottom: 20px; border-width: 2px;">
        <div style="font-size: 24px;">🛡️</div>
        <div>
            <div style="font-weight: 900; font-size: 15px;">وضع حماية النزاهة الأكاديمية مفعّل</div>
            <div style="font-size: 13px; opacity: 0.9; margin-top: 2px;">
                رصد النظام أن لديك اختباراً نشطاً قيد الحل حالياً: <b>{{ $activeAttempt->quiz->title }}</b>.
                تطبيقاً لسياسات الأمانة التعليمية، يمتنع المساعد الآلي عن حل أي أسئلة مباشرة أثناء سير الاختبار.
            </div>
        </div>
    </div>
    @endif

    <!-- Chat Container Card -->
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 620px;">
        <!-- Message Bubbles Scroll Area -->
        <div id="chatBox" style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 16px;">
            @foreach($messages as $msg)
            <div style="display: flex; gap: 10px; {{ $msg->role === 'USER' ? 'flex-direction: row-reverse;' : '' }}">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $msg->role === 'USER' ? 'var(--primary)' : 'rgba(6, 182, 212, 0.15)' }}; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    {{ $msg->role === 'USER' ? '👤' : '🤖' }}
                </div>
                <div style="max-width: 78%; padding: 14px 18px; border-radius: 16px; font-size: 14px; line-height: 1.7;
                    {{ $msg->role === 'USER'
                        ? 'background: linear-gradient(135deg, var(--primary), #8b5cf6); color: #fff; border-bottom-right-radius: 4px;'
                        : 'background: var(--surface); border: 1px solid var(--surface-border); color: var(--text-main); border-bottom-left-radius: 4px;' }}">
                    {!! nl2br(e($msg->content)) !!}
                </div>
            </div>
            @endforeach
        </div>

        <!-- Suggestion Chips -->
        <div style="padding: 10px 20px; border-top: 1px solid var(--surface-border); background: rgba(0, 0, 0, 0.15); display: flex; gap: 8px; overflow-x: auto;">
            <button type="button" class="btn btn-sm" onclick="sendSuggestion('حل المعادلة: 2س + 6 = 18')">
                🧮 حل معادلة رياضية
            </button>
            <button type="button" class="btn btn-sm" onclick="sendSuggestion('اشرح لي قانون أوم في الفيزياء وكيفية حسابه')">
                ⚡ قانون أوم
            </button>
            <button type="button" class="btn btn-sm" onclick="sendSuggestion('ما هو الفرق بين let و const في JavaScript؟')">
                💻 JavaScript let vs const
            </button>
            <button type="button" class="btn btn-sm" onclick="sendSuggestion('ما هي وظيفة الميتوكوندريا في الخلية؟')">
                🔬 الميتوكوندريا
            </button>
        </div>

        <!-- Input Area -->
        <div style="padding: 16px 20px; border-top: 1px solid var(--surface-border); background: var(--surface);">
            <form id="chatForm" onsubmit="handleSend(event)" style="display: flex; gap: 10px;">
                <input type="text"
                       id="promptInput"
                       class="form-control"
                       placeholder="اكتب سؤالك أو استفسارك التعليمي هنا وسيجيبك المعلم فوراً..."
                       required
                       autocomplete="off"
                       style="font-size: 15px; padding: 12px 16px;">
                <button type="submit" id="sendBtn" class="btn btn-primary" style="padding: 12px 24px; white-space: nowrap;">
                    إرسال 🚀
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatBox');
    const promptInput = document.getElementById('promptInput');
    const sendBtn = document.getElementById('sendBtn');
    const csrfToken = '{{ csrf_token() }}';

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    scrollToBottom();

    function formatMarkdown(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Inline code
        escaped = escaped.replace(/`([^`]+)`/g, '<code style="background: rgba(255,255,255,0.08); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px;">$1</code>');
        // Newlines
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    function sendSuggestion(text) {
        promptInput.value = text;
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }

    function appendMessage(role, content) {
        const isUser = (role === 'USER');
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.gap = '10px';
        if (isUser) div.style.flexDirection = 'row-reverse';

        const bubbleContent = isUser ? content.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') : formatMarkdown(content);

        div.innerHTML = `
            <div style="width: 38px; height: 38px; border-radius: 50%; background: ${isUser ? 'var(--primary)' : 'rgba(6, 182, 212, 0.15)'}; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                ${isUser ? '👤' : '🤖'}
            </div>
            <div style="max-width: 78%; padding: 14px 18px; border-radius: 16px; font-size: 14px; line-height: 1.7; ${isUser ? 'background: linear-gradient(135deg, var(--primary), #8b5cf6); color: #fff; border-bottom-right-radius: 4px;' : 'background: var(--surface); border: 1px solid var(--surface-border); color: var(--text-main); border-bottom-left-radius: 4px;'}">
                ${bubbleContent}
            </div>
        `;

        chatBox.appendChild(div);
        scrollToBottom();
    }

    function handleSend(e) {
        e.preventDefault();
        const text = promptInput.value.trim();
        if (!text) return;

        appendMessage('USER', text);
        promptInput.value = '';
        sendBtn.disabled = true;

        // Typing indicator
        const typingIndicator = document.createElement('div');
        typingIndicator.id = 'typingBubble';
        typingIndicator.style.display = 'flex';
        typingIndicator.style.gap = '10px';
        typingIndicator.innerHTML = `
            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(6, 182, 212, 0.15); display: flex; align-items: center; justify-content: center; font-size: 18px;">🤖</div>
            <div style="padding: 12px 18px; border-radius: 16px; font-size: 13px; color: var(--text-muted); background: var(--surface); border: 1px solid var(--surface-border);">
                المعلم الذكي يفكّر ويكتب الإجابة... ✍️
            </div>
        `;
        chatBox.appendChild(typingIndicator);
        scrollToBottom();

        fetch(`{{ route('ai.chat') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('typingBubble')?.remove();
            sendBtn.disabled = false;
            if (data.success) {
                appendMessage('ASSISTANT', data.reply);
            } else {
                appendMessage('ASSISTANT', 'عذراً، حدث خطأ أثناء معالجة السؤال. يرجى المحاولة لاحقاً.');
            }
        })
        .catch(err => {
            document.getElementById('typingBubble')?.remove();
            sendBtn.disabled = false;
            appendMessage('ASSISTANT', 'عذراً، تعذر الاتصال بالمساعد الذكي.');
        });
    }
</script>
@endsection
