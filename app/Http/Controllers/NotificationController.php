<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function index()
    {
        return \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }

    public function markAsRead($id)
    {
        $notification = \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy($id)
    {
        \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}
