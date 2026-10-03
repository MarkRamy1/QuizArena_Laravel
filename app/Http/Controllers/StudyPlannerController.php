<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudyTask;

class StudyPlannerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tasks = $user->studyTasks()->latest()->get();

        $daysOfWeek = [
            'السبت' => 'Saturday',
            'الأحد' => 'Sunday',
            'الإثنين' => 'Monday',
            'الثلاثاء' => 'Tuesday',
            'الأربعاء' => 'Wednesday',
            'الخميس' => 'Thursday',
            'الجمعة' => 'Friday',
        ];

        $totalMinutes = $tasks->sum('duration_minutes');
        $completedMinutes = $tasks->where('is_completed', true)->sum('duration_minutes');
        $completionRate = $tasks->count() > 0 ? (int) round(($tasks->where('is_completed', true)->count() / $tasks->count()) * 100) : 0;

        return view('planner.index', compact('tasks', 'daysOfWeek', 'totalMinutes', 'completedMinutes', 'completionRate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|string',
            'subject' => 'required|string|max:100',
            'topic' => 'required|string|max:150',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'notes' => 'nullable|string|max:300',
        ]);

        Auth::user()->studyTasks()->create([
            'day_of_week' => $validated['day_of_week'],
            'subject' => $validated['subject'],
            'topic' => $validated['topic'],
            'duration_minutes' => $validated['duration_minutes'],
            'notes' => $validated['notes'] ?? null,
            'is_completed' => false,
        ]);

        return back()->with('success', 'تمت إضافة هدف المذاكرة بنجاح إلى جدولك الأسبوعي! 🎯');
    }

    public function toggle(StudyTask $task)
    {
        abort_unless($task->student_id === Auth::id(), 403);

        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        if ($task->is_completed) {
            Auth::user()->addXp(20);
            return back()->with('success', 'أحسنت! تم إكمال المهمة وإضافة 20 XP 🔥');
        }

        return back()->with('info', 'تم إعادة تعيين حالة المهمة.');
    }

    public function destroy(StudyTask $task)
    {
        abort_unless($task->student_id === Auth::id(), 403);
        $task->delete();

        return back()->with('success', 'تم حذف مهمة المذاكرة.');
    }
}
