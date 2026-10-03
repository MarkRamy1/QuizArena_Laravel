<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use App\Models\AuditLog;
use App\Models\FeedbackReport;
use App\Models\Notification;

class AdminController extends Controller
{
    public function __construct()
    {
        // Enforce Admin access
    }

    private function checkAdmin()
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403, 'غير مصرح لك بالوصول إلى لوحة الإدارة.');
    }

    public function index()
    {
        $this->checkAdmin();

        $stats = [
            'total_students' => User::where('role', 'STUDENT')->count(),
            'total_teachers' => User::where('role', 'TEACHER')->count(),
            'total_courses' => Course::count(),
            'total_quizzes' => Quiz::count(),
            'total_attempts' => QuizAttempt::count(),
            'total_certificates' => Certificate::count(),
            'pending_feedback' => FeedbackReport::where('status', 'PENDING')->count(),
        ];

        $recentLogs = AuditLog::with('user')->latest()->take(6)->get();
        $recentUsers = User::latest()->take(5)->get();
        $recentQuizzes = Quiz::with('teacher')->latest()->take(5)->get();

        return view('admin.index', compact('stats', 'recentLogs', 'recentUsers', 'recentQuizzes'));
    }

    public function teachers(Request $request)
    {
        $this->checkAdmin();

        $query = User::where('role', 'TEACHER')
            ->with(['students' => function($q) {
                $q->withCount('quizAttempts')->latest();
            }])
            ->withCount(['students', 'quizzes', 'courses']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('teacher_code', 'like', "%{$s}%");
            });
        }

        $teachers = $query->latest()->paginate(15);

        return view('admin.teachers', compact('teachers'));
    }

    public function users(Request $request)
    {
        $this->checkAdmin();

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $users = $query->latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function toggleUserStatus(User $user)
    {
        $this->checkAdmin();

        $newStatus = ($user->status === 'ACTIVE') ? 'SUSPENDED' : 'ACTIVE';
        $user->update(['status' => $newStatus]);

        AuditLog::log('USER_STATUS_CHANGE', "قام المشرف بتعديل حالة المستخدم {$user->name} إلى {$newStatus}", Auth::id());

        return back()->with('success', "تم تعديل حالة المستخدم {$user->name} إلى: {$newStatus} بنجاح.");
    }

    public function changeUserRole(Request $request, User $user)
    {
        $this->checkAdmin();

        $role = $request->input('role');
        if (in_array($role, ['STUDENT', 'TEACHER', 'ADMIN'])) {
            $user->update(['role' => $role]);
            AuditLog::log('USER_ROLE_CHANGE', "تم تغيير دور المستخدم {$user->name} إلى {$role}", Auth::id());
            return back()->with('success', "تم تحديث دور {$user->name} إلى {$role} بنجاح.");
        }

        return back()->with('error', 'الدور المحدد غير صالح.');
    }

    public function courses()
    {
        $this->checkAdmin();
        $courses = Course::with(['teacher', 'lessons'])->latest()->paginate(15);
        return view('admin.courses', compact('courses'));
    }

    public function toggleCourseStatus(Course $course)
    {
        $this->checkAdmin();
        $newStatus = ($course->status === 'PUBLISHED') ? 'DRAFT' : 'PUBLISHED';
        $course->update(['status' => $newStatus]);

        AuditLog::log('COURSE_STATUS_CHANGE', "قام المشرف بتعديل حالة دورة {$course->title} إلى {$newStatus}", Auth::id());

        return back()->with('success', "تم تعديل حالة الدورة إلى: {$newStatus}");
    }

    public function quizzes()
    {
        $this->checkAdmin();
        $quizzes = Quiz::with(['teacher', 'classroom'])->latest()->paginate(15);
        return view('admin.quizzes', compact('quizzes'));
    }

    public function auditLogs(Request $request)
    {
        $this->checkAdmin();

        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(25);
        return view('admin.audit-logs', compact('logs'));
    }

    public function feedback()
    {
        $this->checkAdmin();
        $reports = FeedbackReport::with(['user', 'resolver'])->latest()->paginate(15);
        return view('admin.feedback', compact('reports'));
    }

    public function resolveFeedback(Request $request, FeedbackReport $report)
    {
        $this->checkAdmin();

        $report->update([
            'status' => 'RESOLVED',
            'admin_notes' => $request->input('admin_notes', 'تمت المراجعة والمعالجة بنجاح.'),
            'resolved_by' => Auth::id(),
        ]);

        Notification::send(
            $report->user_id,
            'تم الرد على بلاغك 🚨',
            "تمت معالجة البلاغ المقدم منك بخصوص ({$report->title}) بواسطة المشرف: " . $report->admin_notes,
            'SUCCESS',
            route('feedback.index')
        );

        AuditLog::log('FEEDBACK_RESOLVED', "تمت معالجة بلاغ رقم #{$report->id} المقدم من المستخدم {$report->user->name}", Auth::id());

        return back()->with('success', 'تم حل البلاغ وإشعار الطالب/المعلم بنجاح!');
    }

    public function settings()
    {
        $this->checkAdmin();
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        $this->checkAdmin();

        AuditLog::log('PLATFORM_SETTINGS_UPDATED', 'قام المشرف بتحديث إعدادات المنصة ومعدلات نقاط التلعيب', Auth::id());

        return back()->with('success', 'تم حفظ إعدادات المنصة بنجاح!');
    }
}
