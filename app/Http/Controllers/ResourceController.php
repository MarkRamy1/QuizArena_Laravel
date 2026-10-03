<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource;
use App\Models\Subject;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::where('status', 'PUBLISHED')->with(['subject', 'teacher']);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $resources = $query->latest()->paginate(9);
        $subjects = Subject::all();

        return view('resources.index', compact('resources', 'subjects'));
    }
}
