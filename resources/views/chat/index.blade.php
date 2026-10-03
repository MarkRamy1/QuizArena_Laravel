@extends('layouts.app')

@section('title', 'ساحة النقاش العامة')

@section('content')
<div style="max-width: 900px; margin: auto;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 8px;">
            <span>💬</span> {{ $room->name }}
        </h1>
        <p style="color: var(--muted); font-size: 14px;">مكان لتبادل النقاشات الأكاديمية والاستفسار عن موضوعات الاختبارات بين الطلاب والمعلمين</p>
    </div>

    <!-- Chat Card Container -->
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 580px;">
        <!-- Messages Stream -->
        <div id="loungeBox" style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 14px;">
            @foreach($messages as $msg)
            @php $isMe = ($msg->sender_id === Auth::id()); @endphp
            <div style="display: flex; gap: 10px; {{ $isMe ? 'flex-direction: row-reverse;' : '' }}">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $isMe ? 'var(--primary)' : 'var(--card2)' }}; border: 1px solid var(--line); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                    {{ $msg->sender->isTeacher() ? '👨‍🏫' : '🎓' }}
                </div>
                <div style="max-width: 75%;">
                    <div style="font-size: 12px; font-weight: 800; color: var(--muted); margin-bottom: 4px; {{ $isMe ? 'text-align: left;' : '' }}">
                        {{ $msg->sender->name }}
                        @if($msg->sender->isTeacher())
                        <span style="color: var(--secondary); font-size: 10px; margin-right: 4px;">(معلم)</span>
                        @endif
                        <span style="font-weight: 400; font-size: 10px; margin-right: 6px;">{{ $msg->created_at->format('H:i') }}</span>
                    </div>
                    <div style="padding: 12px 16px; border-radius: 14px; font-size: 14px; line-height: 1.6;
                        {{ $isMe
                            ? 'background: linear-gradient(135deg, var(--primary), #8b5cf6); color: #fff; border-bottom-right-radius: 4px;'
                            : 'background: var(--card2); border: 1px solid var(--line); color: var(--text); border-bottom-left-radius: 4px;' }}">
                        {{ $msg->body }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Chat Input -->
        <div style="padding: 16px 20px; border-top: 1px solid var(--line); background: var(--card);">
            <form action="{{ route('chat.send') }}" method="POST" style="display: flex; gap: 10px;">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">
                <input type="text"
                       name="body"
                       required
                       placeholder="شارِك برأيك أو اطرح سؤالك في ساحة النقاش..."
                       autocomplete="off"
                       style="font-size: 15px; padding: 13px 16px;">
                <button type="submit" class="btn primary" style="padding: 12px 24px; white-space: nowrap;">
                    نشر 📤
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const loungeBox = document.getElementById('loungeBox');
    if (loungeBox) {
        loungeBox.scrollTop = loungeBox.scrollHeight;
    }
</script>
@endsection
