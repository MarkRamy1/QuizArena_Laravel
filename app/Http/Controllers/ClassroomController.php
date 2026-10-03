<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('q');

        if ($user->isTeacher()) {
            $classrooms = Classroom::where('teacher_id', $user->id)
                ->withCount(['students', 'assignments', 'quizzes'])
                ->latest()
                ->get();

            return view('classrooms.index', compact('classrooms'));
        }

        // Student view
        $enrolledClassrooms = $user->enrolledClassrooms()
            ->with('teacher')
            ->withCount(['assignments', 'quizzes'])
            ->get();

        $pendingClassrooms = Classroom::whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'PENDING');
        })->with('teacher')->get();

        // Search results if searched
        $searchResults = collect();
        if ($search) {
            $searchResults = Classroom::where('name', 'LIKE', "%{$search}%")
                ->orWhere('subject', 'LIKE', "%{$search}%")
                ->orWhereHas('teacher', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->with('teacher')
                ->withCount('students')
                ->get();
        }

        return view('classrooms.index', compact('enrolledClassrooms', 'pendingClassrooms', 'searchResults', 'search'));
    }

    public function create()
    {
        if (!Auth::user()->isTeacher()) {
            abort(403, 'Only teachers can create classrooms.');
        }

        return view('classrooms.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isTeacher()) {
            abort(403, 'Only teachers can create classrooms.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:100',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'theme_color' => 'nullable|string|max:20',
        ]);

        // Generate unique code (e.g. CLS-8A4F2)
        do {
            $code = 'CLS-' . strtoupper(Str::random(6));
        } while (Classroom::where('code', $code)->exists());

        $classroom = Classroom::create([
            'teacher_id' => Auth::id(),
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'grade' => $validated['grade'] ?? null,
            'description' => $validated['description'] ?? null,
            'theme_color' => $validated['theme_color'] ?? '#4f46e5',
            'code' => $code,
        ]);

        return redirect()->route('classrooms.show', $classroom)->with('success', __('app.create_classroom') . ' - ' . $classroom->name);
    }

    public function show(Classroom $classroom)
    {
        $user = Auth::user();

        // Authorization check
        $isTeacher = ($classroom->teacher_id === $user->id);
        $isMember = $classroom->students()->where('users.id', $user->id)->exists();

        if (!$isTeacher && !$isMember) {
            abort(403, 'You are not enrolled in this classroom.');
        }

        $classroom->load([
            'teacher',
            'students',
            'materials.teacher',
            'assignments' => function ($q) {
                $q->withCount('submissions');
            },
            'quizzes' => function ($q) {
                $q->withCount('attempts');
            },
            'messages' => function ($q) {
                $q->with('user')->latest()->take(50);
            },
        ]);

        $pendingMembers = collect();
        if ($isTeacher) {
            $pendingMembers = $classroom->pendingRequests()->get();
        }

        // Student's submissions in this classroom
        $studentSubmissions = collect();
        $studentAttempts = collect();
        if (!$isTeacher) {
            $studentSubmissions = $user->assignmentSubmissions()
                ->whereIn('assignment_id', $classroom->assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $studentAttempts = $user->quizAttempts()
                ->whereIn('quiz_id', $classroom->quizzes->pluck('id'))
                ->get()
                ->groupBy('quiz_id');
        }

        return view('classrooms.show', compact(
            'classroom',
            'isTeacher',
            'pendingMembers',
            'studentSubmissions',
            'studentAttempts'
        ));
    }

    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $classroom = Classroom::whereRaw('UPPER(code) = ?', [$code])->first();

        if (!$classroom) {
            return back()->withErrors(['code' => __('app.error') . ': Invalid classroom code / رمز الفصل غير صحيح.']);
        }

        $user = Auth::user();

        if ($classroom->teacher_id === $user->id) {
            return redirect()->route('classrooms.show', $classroom)->with('info', 'You are the teacher of this classroom.');
        }

        $existing = ClassroomMember::where('classroom_id', $classroom->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'ACCEPTED') {
                return redirect()->route('classrooms.show', $classroom)->with('info', 'You are already enrolled in this class.');
            }
            $existing->update(['status' => 'ACCEPTED', 'joined_at' => now()]);
            return redirect()->route('classrooms.show', $classroom)->with('success', 'Joined classroom successfully!');
        }

        ClassroomMember::create([
            'classroom_id' => $classroom->id,
            'user_id' => $user->id,
            'status' => 'ACCEPTED',
            'joined_at' => now(),
        ]);

        return redirect()->route('classrooms.show', $classroom)->with('success', 'Successfully joined ' . $classroom->name);
    }

    public function requestJoin(Classroom $classroom)
    {
        $user = Auth::user();

        if ($classroom->teacher_id === $user->id) {
            return redirect()->route('classrooms.show', $classroom);
        }

        $existing = ClassroomMember::where('classroom_id', $classroom->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'ACCEPTED') {
                return redirect()->route('classrooms.show', $classroom);
            }
            return back()->with('info', __('app.request_pending'));
        }

        ClassroomMember::create([
            'classroom_id' => $classroom->id,
            'user_id' => $user->id,
            'status' => 'PENDING',
        ]);

        return back()->with('success', __('app.request_pending') . ': Join request sent to teacher.');
    }

    public function handleRequest(Classroom $classroom, User $student, Request $request)
    {
        if ($classroom->teacher_id !== Auth::id()) {
            abort(403);
        }

        $action = $request->input('action'); // accept or reject

        $member = ClassroomMember::where('classroom_id', $classroom->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        if ($action === 'accept') {
            $member->update([
                'status' => 'ACCEPTED',
                'joined_at' => now(),
            ]);
            return back()->with('success', "Accepted {$student->name} into {$classroom->name}.");
        } else {
            $member->delete();
            return back()->with('info', "Rejected request from {$student->name}.");
        }
    }
}
