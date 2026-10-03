<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Classroom;
use App\Models\Material;

class MaterialController extends Controller
{
    public function store(Request $request, Classroom $classroom)
    {
        $user = Auth::user();

        if ($classroom->teacher_id !== $user->id) {
            abort(403, 'Only classroom teacher can upload materials.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'material_file' => 'nullable|file|max:51200', // 50MB
            'external_url' => 'nullable|url|max:500',
        ]);

        $filePath = null;
        $fileName = null;
        $fileType = null;
        $fileSize = null;

        if ($request->hasFile('material_file')) {
            $file = $request->file('material_file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $filePath = $file->store('materials', 'public');
        }

        Material::create([
            'classroom_id' => $classroom->id,
            'teacher_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'external_url' => $validated['external_url'] ?? null,
        ]);

        return back()->with('success', __('app.upload_material') . ' - ' . $validated['title']);
    }

    public function download(Material $material)
    {
        $user = Auth::user();
        $classroom = $material->classroom;

        $isTeacher = ($classroom->teacher_id === $user->id);
        $isMember = $classroom->students()->where('users.id', $user->id)->exists();

        if (!$isTeacher && !$isMember) {
            abort(403);
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found on server.');
        }

        return Storage::disk('public')->download($material->file_path, $material->file_name ?? 'material_document');
    }

    public function destroy(Material $material)
    {
        $user = Auth::user();
        if ($material->teacher_id !== $user->id) {
            abort(403);
        }

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return back()->with('success', 'Material deleted successfully.');
    }
}
