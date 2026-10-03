<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\User;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        $tab = $request->input('tab', 'all');

        $courses = collect();
        $quizzes = collect();
        $lessons = collect();
        $teachers = collect();

        if (!empty($q)) {
            if ($tab === 'all' || $tab === 'courses') {
                $courses = Course::with(['teacher', 'lessons'])
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->take(8)
                    ->get();
            }

            if ($tab === 'all' || $tab === 'quizzes') {
                $quizzes = Quiz::with(['teacher', 'classroom'])
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->take(8)
                    ->get();
            }

            if ($tab === 'all' || $tab === 'lessons') {
                $lessons = Lesson::with('course')
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->take(8)
                    ->get();
            }

            if ($tab === 'all' || $tab === 'teachers') {
                $teachers = User::where('role', 'TEACHER')
                    ->where(function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                              ->orWhere('bio', 'like', "%{$q}%");
                    })
                    ->take(6)
                    ->get();
            }
        }

        $totalResults = $courses->count() + $quizzes->count() + $lessons->count() + $teachers->count();

        return view('search.index', compact('q', 'tab', 'courses', 'quizzes', 'lessons', 'teachers', 'totalResults'));
    }
}
