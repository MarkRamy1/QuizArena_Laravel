<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            // Auto-generate teacher code if teacher logs in and doesn't have one
            if ($user->isTeacher()) {
                $user->generateTeacherCode();
            }

            if ($user->locale) {
                session(['locale' => $user->locale]);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed') ?? 'Invalid credentials / بيانات الدخول غير صحيحة.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:STUDENT,TEACHER',
            'teacher_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_email' => 'nullable|email|max:150',
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_relation' => 'nullable|string|max:50',
        ]);

        $teacherId = null;
        if ($validated['role'] === 'STUDENT' && !empty($validated['teacher_code'])) {
            $matchedTeacher = User::where('role', 'TEACHER')
                ->where('teacher_code', trim(strtoupper($validated['teacher_code'])))
                ->first();
            if ($matchedTeacher) {
                $teacherId = $matchedTeacher->id;
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'teacher_id' => $teacherId,
            'phone' => $validated['phone'] ?? null,
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_email' => $validated['guardian_email'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'guardian_relation' => $validated['guardian_relation'] ?? null,
            'status' => 'ACTIVE',
            'locale' => session('locale', 'ar'),
            'last_login_at' => now(),
        ]);

        // Auto-generate teacher code if registering as a teacher
        if ($user->isTeacher()) {
            $user->generateTeacherCode();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', __('app.welcome_back') . ' ' . $user->name);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
