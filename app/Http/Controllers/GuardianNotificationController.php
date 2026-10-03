<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\GuardianNotification;

class GuardianNotificationController extends Controller
{
    /**
     * Send Absence / Alert notification to student's guardian by Teacher.
     */
    public function sendAlert(Request $request)
    {
        $teacher = Auth::user();
        if (!$teacher->isTeacher()) {
            abort(403, 'فقط المعلم يمكنه إرسال تنبيهات لأولياء الأمور.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'quiz_id' => 'nullable|exists:quizzes,id',
            'type' => 'required|in:SESSION_MISSED,EXAM_MISSED,CUSTOM',
            'custom_note' => 'nullable|string|max:1000',
        ]);

        $student = User::findOrFail($validated['student_id']);

        if (empty($student->guardian_email) && empty($student->guardian_phone)) {
            return back()->with('error', "تنبيه: الطالب ({$student->name}) لم يقم بإدخال بيانات الاتصال بولي أمره بعد في حسابه.");
        }

        $title = '';
        $message = '';
        $classroom = !empty($validated['classroom_id']) ? Classroom::find($validated['classroom_id']) : null;
        $quiz = !empty($validated['quiz_id']) ? Quiz::find($validated['quiz_id']) : null;

        if ($validated['type'] === 'SESSION_MISSED') {
            $title = '⚠️ إشعار غياب عن الحصة الدراسية: ' . ($classroom ? $classroom->name : 'الفصل الدراسي');
            $message = "السيد/ة ولي أمر الطالب ({$student->name}) المحترم،\nنود إفادتكم بأن الطالب تغيب عن حضور الجلسة/الحصة الدراسية المقررة اليوم في مادة (" . ($classroom ? $classroom->subject : 'الدراسة') . "). نرجو المتابعة وحث الطالب على الالتزام بالحضور حرصاً على مستواه الأكاديمي.";
        } elseif ($validated['type'] === 'EXAM_MISSED') {
            $title = '🚨 تنبيه عاجل: تغيب الطالب عن موعد الاختبار: ' . ($quiz ? $quiz->title : 'الاختبار المقرر');
            $message = "السيد/ة ولي أمر الطالب ({$student->name}) المحترم،\nنحيطكم علماً بأن الطالب لم يقم بأداء الاختبار التقييمي المقرر بعنوان [" . ($quiz ? $quiz->title : 'الاختبار') . "] حتى موعد الاستحقاق. يرجى التواصل مع المعلم المشرف فوراً لتدارك الأمر.";
        } else {
            $title = '📢 رسالة متابعة من المعلم المشرف (' . $teacher->name . ')';
            $message = "السيد/ة ولي أمر الطالب ({$student->name}) المحترم،\n" . ($validated['custom_note'] ?? 'نأمل متابعة تقدم الطالب الأكاديمي والواجبات المطلوبة.');
        }

        if (!empty($validated['custom_note']) && $validated['type'] !== 'CUSTOM') {
            $message .= "\n\nملاحظة إضافية من المعلم: " . $validated['custom_note'];
        }

        GuardianNotification::create([
            'student_id' => $student->id,
            'classroom_id' => $classroom?->id,
            'quiz_id' => $quiz?->id,
            'teacher_id' => $teacher->id,
            'type' => $validated['type'],
            'guardian_name' => $student->guardian_name,
            'guardian_email' => $student->guardian_email,
            'guardian_phone' => $student->guardian_phone,
            'title' => $title,
            'message' => $message,
            'status' => 'SENT',
            'sent_at' => now(),
        ]);

        return back()->with('success', "تم إرسال إشعار فوري بنجاح إلى ولي أمر الطالب ({$student->name}) عبر البريد ورسائل الهاتف المسجلة ✅");
    }

    /**
     * View history of guardian notifications for a classroom or student.
     */
    public function log(Request $request)
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            $notifications = GuardianNotification::where('teacher_id', $user->id)
                ->with(['student', 'classroom', 'quiz'])
                ->latest()
                ->paginate(20);
        } else {
            $notifications = GuardianNotification::where('student_id', $user->id)
                ->with(['classroom', 'quiz', 'teacher'])
                ->latest()
                ->paginate(20);
        }

        return view('guardian.log', compact('notifications'));
    }
}
