<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FeedbackReport;
use App\Models\AuditLog;
use App\Models\Notification;

class FeedbackController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $reports = FeedbackReport::with(['user', 'resolver'])->latest()->paginate(15);
        } else {
            $reports = FeedbackReport::where('user_id', $user->id)->latest()->paginate(10);
        }

        return view('feedback.index', compact('reports'));
    }

    public function create()
    {
        return view('feedback.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:QUESTION,QUIZ,LESSON,COURSE,GENERAL',
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:1000',
            'item_id' => 'nullable|integer',
        ]);

        $report = FeedbackReport::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'item_id' => $validated['item_id'] ?? null,
            'status' => 'PENDING',
        ]);

        AuditLog::log('FEEDBACK_SUBMITTED', "قدم المستخدم " . Auth::user()->name . " بلاغاً جديداً: {$report->title}", Auth::id());

        return redirect()->route('feedback.index')->with('success', 'شكراً لك! تم استلام بلاغك وسيقوم الفريق الأكاديمي بمراجعته وحله في أقرب وقت 🚀');
    }
}
