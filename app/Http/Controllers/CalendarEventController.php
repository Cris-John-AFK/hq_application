<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // Include recurring events regardless of date? 
        // For simplicity v1: fetch events overlapping range OR is_recurring = true
        if ($request->filled('start_date')) {
             $query->orWhere('is_recurring', true);
        }

        return response()->json($query->orderBy('start_date')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:event,holiday,meeting',
            'is_recurring' => 'boolean'
        ]);

        $validated['created_by'] = Auth::id();

        $event = CalendarEvent::create($validated);

        return response()->json($event, 201);
    }

    public function destroy($id)
    {
        $event = CalendarEvent::findOrFail($id);
        
        // Authorization check: Only admin or creator
        if (Auth::user()->role !== 'admin' && Auth::id() !== $event->created_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted']);
    }
}
