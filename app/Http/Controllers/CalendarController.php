<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;

class CalendarController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::where('status', 'PUBLISHED')
            ->whereNotNull('starts_at')
            ->orderBy('starts_at')
            ->get();

        return view('calendar.index', compact('quizzes'));
    }
}
