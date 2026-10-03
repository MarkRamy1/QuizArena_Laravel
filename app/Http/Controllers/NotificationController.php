<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->inAppNotifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update(['is_read' => true]);

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back()->with('success', 'تم تحديد الإشعار كمقروء.');
    }

    public function markAllAsRead()
    {
        Auth::user()->inAppNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }
}
