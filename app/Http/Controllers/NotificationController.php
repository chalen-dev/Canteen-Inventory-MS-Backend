<?php

// app/Http/Controllers/NotificationController.php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }
        $notification->update(['read' => true]);
        return response()->json($notification);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->where('read', false)->update(['read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }
}
