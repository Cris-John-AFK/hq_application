<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $query = CalendarEvent::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            });
        }

        $events = $query->orderBy('start_date')->get();
        $unreadCount = CalendarEvent::where('is_read', false)->count();

        return response()->json([
            'events' => $events,
            'unread_count' => $unreadCount
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:event,holiday,meeting'
        ]);

        $event = CalendarEvent::create($validated);

        \App\Utils\AuditLogger::log('Schedule', 'Created', "Added new calendar event/meeting: {$event->title}.");

        return response()->json($event, 201);
    }

    public function markAsRead()
    {
        CalendarEvent::where('is_read', false)->update(['is_read' => true]);
        return response()->json(['message' => 'All events marked as read']);
    }

    public function destroy($id)
    {
        $event = CalendarEvent::findOrFail($id);
        $title = $event->title;
        $event->delete();

        // Audit Log
        \App\Utils\AuditLogger::log('Schedule', 'Deleted', "Deleted calendar event/meeting: {$title}.");

        return response()->json(['message' => 'Event deleted']);
    }
}
