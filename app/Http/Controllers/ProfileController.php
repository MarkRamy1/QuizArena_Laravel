<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string|max:1000',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_email' => 'nullable|email|max:150',
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_relation' => 'nullable|string|max:50',
            'locale' => 'required|in:ar,en',
        ]);

        $user->update($validated);
        session(['locale' => $validated['locale']]);

        return back()->with('success', __('app.profile_updated_successfully'));
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists on disk
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return back()->with('success', __('app.avatar_updated_successfully'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', __('app.password_updated_successfully'));
    }

    public function setLocale($locale)
    {
        if (in_array($locale, ['ar', 'en'])) {
            session(['locale' => $locale]);
            if (Auth::check()) {
                Auth::user()->update(['locale' => $locale]);
            }
        }

        return back();
    }
}
