<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Classroom;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;

class AssignmentController extends Controller
{
    public function create(Classroom $classroom)
    {
        $user = Auth::user();
        if ($classroom->teacher_id !== $user->id) {
            abort(403);
        }

        return view('assignments.create', compact('classroom'));
    }

    public function store(Request $request, Classroom $classroom)
    {
        $user = Auth::user();
        if ($classroom->teacher_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'max_points' => 'required|integer|min:1|max:1000',
            'assignment_file' => 'nullable|file|max:20480',
        ]);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('assignment_file')) {
            $file = $request->file('assignment_file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('assignments/prompts', 'public');
        }

        $assignment = Assignment::create([
            'classroom_id' => $classroom->id,
            'teacher_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'] ?? null,
            'max_points' => $validated['max_points'],
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        return redirect()->route('classrooms.show', $classroom)->with('success', __('app.create_assignment') . ' - ' . $assignment->title);
    }

    public function show(Assignment $assignment)
    {
        $user = Auth::user();
        $classroom = $assignment->classroom;

        $isTeacher = ($classroom->teacher_id === $user->id);
        $isMember = $classroom->students()->where('users.id', $user->id)->exists();

        if (!$isTeacher && !$isMember) {
            abort(403);
        }

        $submissions = collect();
        $mySubmission = null;

        if ($isTeacher) {
            $submissions = $assignment->submissions()->with('student')->latest()->get();
        } else {
            $mySubmission = $assignment->submissions()->where('student_id', $user->id)->first();
        }

        return view('assignments.show', compact('assignment', 'classroom', 'isTeacher', 'submissions', 'mySubmission'));
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $classroom = $assignment->classroom;

        if (!$classroom->students()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'submission_file' => 'required|file|max:25600', // 25MB
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('submission_file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('assignments/submissions', 'public');

        $status = ($assignment->due_date && now()->greaterThan($assignment->due_date)) ? 'LATE' : 'SUBMITTED';

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $user->id],
            [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'notes' => $request->input('notes'),
                'status' => $status,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', __('app.submit_assignment') . ' - ' . __('app.submitted'));
    }

    public function grade(Request $request, AssignmentSubmission $submission)
    {
        $user = Auth::user();
        $classroom = $submission->assignment->classroom;

        if ($classroom->teacher_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'score' => 'required|integer|min:0|max:' . $submission->assignment->max_points,
            'feedback' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'GRADED',
            'graded_at' => now(),
        ]);

        return back()->with('success', __('app.grade_submission') . ' - ' . __('app.success'));
    }

    public function downloadReference(Assignment $assignment)
    {
        if (!$assignment->file_path || !Storage::disk('public')->exists($assignment->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($assignment->file_path, $assignment->file_name ?? 'reference_file');
    }

    public function downloadSubmission(AssignmentSubmission $submission)
    {
        $user = Auth::user();
        $isTeacher = ($submission->assignment->teacher_id === $user->id);
        $isStudent = ($submission->student_id === $user->id);

        if (!$isTeacher && !$isStudent) {
            abort(403);
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($submission->file_path, $submission->file_name ?? 'submission_file');
    }
}
