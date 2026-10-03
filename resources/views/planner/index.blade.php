@extends('layouts.app')

@section('title', 'جدول وخطة المذاكرة الأسبوعية — Study Planner')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Header & Planner KPIs -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; display: flex; align-items: center; gap: 10px;">
                <span>📅</span> جدول وخطة المذاكرة الذكية (Study Planner)
            </h1>
            <p style="color: var(--text-muted); font-size: 14px;">نظّم أوقات مراجعتك الأسبوعية، تابع نسبة التزامك بالأهداف، واكسب XP إضافياً لكل مهمة تنجزها</p>
        </div>

        <button type="button" class="btn btn-primary" onclick="document.getElementById('taskModal').style.display='flex'">
            ➕ إضافة هدف مذاكرة
        </button>
    </div>

    <!-- Planner Stats Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="card" style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 32px; background: rgba(79, 70, 229, 0.12); width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                ⏱️
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">إجمالي وقت المذاكرة المخطط</div>
                <div style="font-size: 22px; font-weight: 900; color: var(--primary);">{{ $totalMinutes }} دقيقة</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 32px; background: rgba(16, 185, 129, 0.12); width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                ✅
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">الوقت المنجز بنجاح</div>
                <div style="font-size: 22px; font-weight: 900; color: var(--success);">{{ $completedMinutes }} دقيقة</div>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 32px; background: rgba(245, 158, 11, 0.12); width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
                📈
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">نسبة الإنجاز الأسبوعية</div>
                <div style="font-size: 22px; font-weight: 900; color: var(--warning);">{{ $completionRate }}%</div>
            </div>
        </div>
    </div>

    <!-- Tasks by Day or Table -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> جدول المهام الدراسية الموزعة على أيام الأسبوع
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($tasks as $t)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: var(--surface); border: 1px solid {{ $t->is_completed ? 'rgba(16, 185, 129, 0.3)' : 'var(--surface-border)' }}; border-radius: var(--radius-md); flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <!-- Toggle Checkbox Form -->
                    <form action="{{ route('planner.toggle', $t->id) }}" method="POST">
                        @csrf
                        <button type="submit" style="background: none; border: none; font-size: 24px; cursor: pointer;" title="{{ $t->is_completed ? 'إلغاء التحديد' : 'تحديد كمكتمل' }}">
                            {{ $t->is_completed ? '✅' : '⬜' }}
                        </button>
                    </form>

                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="role-pill" style="background: rgba(79, 70, 229, 0.15);">{{ $t->day_of_week }}</span>
                            <span class="role-pill">{{ $t->subject }}</span>
                            <span style="font-weight: 800; font-size: 14.5px; {{ $t->is_completed ? 'text-decoration: line-through; opacity: 0.7;' : '' }}">
                                {{ $t->topic }}
                            </span>
                        </div>
                        @if($t->notes)
                        <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 4px;">
                            💡 {{ $t->notes }}
                        </div>
                        @endif
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 13px; font-weight: 700; color: var(--accent);">
                        ⏱️ {{ $t->duration_minutes }} دقيقة
                    </span>

                    <form action="{{ route('planner.destroy', $t->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذه المهمة من الجدول؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px; font-size: 12px;">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 40px; margin-bottom: 8px;">📅</div>
                <div style="font-size: 16px; font-weight: 700;">جدولك فارغ حالياً!</div>
                <p style="font-size: 13px; margin-top: 4px;">اضغط على "إضافة هدف مذاكرة" لتنظيم مواعيد استذكار المواد لهذا الأسبوع.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal: Add Task -->
<div id="taskModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: min(500px, 100%); padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="font-size: 18px; font-weight: 800;">➕ إضافة هدف مذاكرة جديد</h3>
            <button type="button" onclick="document.getElementById('taskModal').style.display='none'" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;">✕</button>
        </div>

        <form action="{{ route('planner.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">اليوم *</label>
                <select name="day_of_week" class="form-control" required>
                    @foreach($daysOfWeek as $arDay => $enDay)
                    <option value="{{ $arDay }}">{{ $arDay }} ({{ $enDay }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">المادة الدراسية *</label>
                    <input type="text" name="subject" class="form-control" placeholder="مثال: الفيزياء، الرياضيات" required>
                </div>
                <div class="form-group">
                    <label class="form-label">وقت المذاكرة (بالدقائق) *</label>
                    <input type="number" name="duration_minutes" class="form-control" value="45" min="5" max="300" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">الموضوع أو الهدف المحدد *</label>
                <input type="text" name="topic" class="form-control" placeholder="مثال: حل 20 مسألة على قوانين نيوتن" required>
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات إضافية (اختياري)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="أي روابط أو تذكير خاص بالهدف..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                <button type="button" onclick="document.getElementById('taskModal').style.display='none'" class="btn">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ في الجدول 🎯</button>
            </div>
        </form>
    </div>
</div>
@endsection
