<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Classroom;
use App\Models\ClassroomMessage;
use App\Models\PrivateMessage;
use App\Models\User;

class ChatController extends Controller
{
    /**
     * Send message to the classroom global chat wall.
     */
    public function postClassroomMessage(Request $request, Classroom $classroom)
    {
        $user = Auth::user();

        // Check if member or teacher
        $isTeacher = ($classroom->teacher_id === $user->id);
        $isMember = $classroom->students()->where('users.id', $user->id)->exists();

        if (!$isTeacher && !$isMember) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:3000',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        ClassroomMessage::create([
            'classroom_id' => $classroom->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
        ]);

        return back()->with('success', 'Message posted to class stream.');
    }

    /**
     * View private direct messaging inbox and active conversation.
     */
    public function privateChat(?User $recipient = null)
    {
        $user = Auth::user();

        // Find all contacts who have chatted with user or are in the same classrooms
        // 1. Teachers of user's classrooms (if student) OR Students of teacher's classrooms (if teacher)
        if ($user->isTeacher()) {
            $classroomIds = $user->createdClassrooms->pluck('id');
            $contacts = User::whereHas('enrolledClassrooms', function ($q) use ($classroomIds) {
                $q->whereIn('classrooms.id', $classroomIds)->where('status', 'ACCEPTED');
            })->distinct()->get();
        } else {
            $classroomIds = $user->enrolledClassrooms()->pluck('classrooms.id');
            $contacts = User::whereHas('createdClassrooms', function ($q) use ($classroomIds) {
                $q->whereIn('id', $classroomIds);
            })->distinct()->get();
        }

        // Also add any other users who sent or received private messages
        $messagedUserIds = PrivateMessage::where('sender_id', $user->id)
            ->pluck('receiver_id')
            ->merge(
                PrivateMessage::where('receiver_id', $user->id)->pluck('sender_id')
            )->unique();

        $additionalContacts = User::whereIn('id', $messagedUserIds)->get();
        $contacts = $contacts->merge($additionalContacts)->unique('id');

        $activeConversation = null;
        $messages = collect();

        if ($recipient) {
            $activeConversation = $recipient;

            // Mark received unread messages as read
            PrivateMessage::where('sender_id', $recipient->id)
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            // Fetch chat history
            $messages = PrivateMessage::where(function ($q) use ($user, $recipient) {
                $q->where('sender_id', $user->id)->where('receiver_id', $recipient->id);
            })->orWhere(function ($q) use ($user, $recipient) {
                $q->where('sender_id', $recipient->id)->where('receiver_id', $user->id);
            })->with(['sender', 'receiver'])->oldest()->take(100)->get();
        } elseif ($contacts->isNotEmpty()) {
            // Default to first contact
            return redirect()->route('chat.private', $contacts->first()->id);
        }

        return view('chat.private', compact('contacts', 'activeConversation', 'messages'));
    }

    /**
     * Send private 1-on-1 message.
     */
    public function sendPrivateMessage(Request $request, User $recipient)
    {
        $user = Auth::user();

        if ($user->id === $recipient->id) {
            return back()->withErrors(['message' => 'Cannot message yourself.']);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:3000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        PrivateMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $recipient->id,
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
        ]);

        return redirect()->route('chat.private', $recipient->id);
    }
}
