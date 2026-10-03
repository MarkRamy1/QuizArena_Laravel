<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Certificate;

class CertificateController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            $certificates = Certificate::where('teacher_id', $user->id)
                ->with(['quiz', 'student'])
                ->latest()
                ->paginate(12);
        } else {
            $certificates = Certificate::where('student_id', $user->id)
                ->with(['quiz', 'teacher'])
                ->latest()
                ->paginate(12);
        }

        return view('certificates.index', compact('certificates'));
    }

    public function show($id)
    {
        $certificate = Certificate::with(['student', 'quiz', 'teacher', 'attempt'])->findOrFail($id);
        return view('certificates.show', compact('certificate'));
    }

    public function verify(Request $request, $code = null)
    {
        $verificationCode = $code ?? $request->input('code');
        $certificate = null;
        $searched = false;

        if ($verificationCode) {
            $searched = true;
            $cleanCode = strtoupper(trim($verificationCode));
            $certificate = Certificate::where('verification_code', $cleanCode)
                ->with(['student', 'quiz', 'teacher'])
                ->first();
        }

        return view('certificates.verify', compact('certificate', 'searched', 'verificationCode'));
    }
}
