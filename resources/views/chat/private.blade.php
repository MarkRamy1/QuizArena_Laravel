@extends('layouts.app')

@section('title', __('app.private_chat'))

@section('content')
<div style="height: calc(100vh - 170px); display: grid; grid-template-columns: 320px 1fr; gap: 20px;">

    <!-- Left Contacts List -->
    <div class="card" style="padding: 16px; display: flex; flex-direction: column; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <span>💬</span> {{ __('app.private_chat') }}
            </h3>
            <span class="role-pill" style="font-size: 11px;">{{ $contacts->count() }}</span>
        </div>

        <div style="overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 6px;">
            @if($contacts->isEmpty())
            <div style="text-align: center; padding: 30px 10px; color: var(--text-muted); font-size: 13.5px;">
                {{ __('app.no_records') }}
                <p style="margin-top: 6px; font-size: 12px;">انضم إلى فصولك أولاً لتتمكن من مراسلة المعلمين والطلاب.</p>
            </div>
            @else
            @foreach($contacts as $contact)
            @php
                $isActive = $activeConversation && ($activeConversation->id === $contact->id);
            @endphp
            <a href="{{ route('chat.private', $contact->id) }}" 
               style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: var(--radius-md); transition: all 0.2s; background: {{ $isActive ? 'var(--surface-hover)' : 'transparent' }}; border: 1px solid {{ $isActive ? 'var(--primary)' : 'transparent' }};">
                <img src="{{ $contact->avatar_url }}" alt="{{ $contact->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $contact->name }}
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted);">
                        @if($contact->isTeacher()) 👨‍🏫 {{ __('app.role_teacher') }} @else 🎓 {{ __('app.role_student') }} @endif
                    </div>
                </div>
            </a>
            @endforeach
            @endif
        </div>
    </div>

    <!-- Right Conversation Box -->
    <div class="card" style="padding: 0; display: flex; flex-direction: column; overflow: hidden;">
        @if($activeConversation)
        <!-- Conversation Header -->
        <div style="padding: 16px 20px; background: var(--surface); border-bottom: 1px solid var(--surface-border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="{{ $activeConversation->avatar_url }}" alt="{{ $activeConversation->name }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800;">{{ $activeConversation->name }}</h3>
                    <span style="font-size: 12px; color: var(--text-muted);">
                        @if($activeConversation->isTeacher()) 👨‍🏫 {{ __('app.role_teacher') }} @else 🎓 {{ __('app.role_student') }} @endif &bull; {{ $activeConversation->email }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Messages Flow -->
        <div id="messagesContainer" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px;">
            @if($messages->isEmpty())
            <div style="text-align: center; margin: auto; color: var(--text-muted);">
                <div style="font-size: 40px; margin-bottom: 8px;">👋</div>
                <p>{{ __('app.no_messages_yet') }}</p>
            </div>
            @else
            @foreach($messages as $msg)
            @php
                $isMine = ($msg->sender_id === Auth::id());
            @endphp
            <div style="display: flex; justify-content: {{ $isMine ? 'flex-end' : 'flex-start' }};">
                <div style="max-width: 70%; display: flex; flex-direction: column; align-items: {{ $isMine ? 'flex-end' : 'flex-start' }};">
                    <div style="padding: 12px 16px; border-radius: var(--radius-lg); font-size: 14.5px; line-height: 1.5; white-space: pre-line;
                                background: {{ $isMine ? 'linear-gradient(135deg, var(--primary), var(--primary-light))' : 'var(--surface)' }};
                                color: {{ $isMine ? '#fff' : 'var(--text-main)' }};
                                border: {{ $isMine ? 'none' : '1px solid var(--surface-border)' }};
                                box-shadow: var(--shadow-sm);">
                        {{ $msg->message }}

                        @if($msg->attachment)
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.2);">
                            <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: inherit; text-decoration: underline;">
                                📎 {{ __('app.download_file') }}
                            </a>
                        </div>
                        @endif
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted); margin-top: 4px; padding: 0 4px;">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <!-- Send Message Box -->
        <div style="padding: 16px 20px; background: var(--surface); border-top: 1px solid var(--surface-border);">
            <form action="{{ route('chat.sendPrivate', $activeConversation) }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                @csrf
                <label style="cursor: pointer; font-size: 20px; color: var(--text-muted); padding: 6px 10px;" title="{{ __('app.attach_file') }}">
                    📎
                    <input type="file" name="attachment" style="display: none;" onchange="alert('Attached: ' + (this.files[0]?.name || ''))">
                </label>

                <input type="text" name="message" class="form-control" placeholder="{{ __('app.type_message') }}..." required autocomplete="off" style="flex: 1;">

                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    🚀 {{ __('app.send') }}
                </button>
            </form>
        </div>
        @else
        <!-- No Contact Selected -->
        <div style="text-align: center; margin: auto; padding: 40px; color: var(--text-muted);">
            <div style="font-size: 48px; margin-bottom: 12px;">✉️</div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">{{ __('app.select_conversation') }}</h3>
            <p style="font-size: 13.5px;">اختر معلماً أو طالباً من القائمة الجانبية لبدء المحادثة الفورية الخاصة.</p>
        </div>
        @endif
    </div>

</div>

<script>
    // Auto-scroll chat to bottom
    const box = document.getElementById('messagesContainer');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }
</script>
@endsection
