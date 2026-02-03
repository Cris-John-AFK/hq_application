<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return \App\Models\Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function all()
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);
        return \App\Models\Announcement::with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success,urgent',
            'priority' => 'required|integer',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $announcement = \App\Models\Announcement::create(array_merge($validated, [
            'created_by' => \Illuminate\Support\Facades\Auth::id()
        ]));

        // Audit Log
        \App\Utils\AuditLogger::log('Bulletins', 'Created', "Published a new bulletin: {$announcement->title}.");

        return response()->json($announcement, 201);
    }

    public function update(Request $request, $id)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);

        $announcement = \App\Models\Announcement::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:info,warning,success,urgent',
            'priority' => 'sometimes|required|integer',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean'
        ]);

        $announcement->update($validated);

        // Audit Log
        \App\Utils\AuditLogger::log('Bulletins', 'Updated', "Updated bulletin: {$announcement->title}.");

        return response()->json($announcement);
    }

    public function destroy($id)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);
        
        $announcement = \App\Models\Announcement::findOrFail($id);
        $title = $announcement->title;
        $announcement->delete();
        
        // Audit Log
        \App\Utils\AuditLogger::log('Bulletins', 'Deleted', "Deleted bulletin: {$title}.");
        
        return response()->json(['message' => 'Announcement deleted']);
    }

    public function toggle($id)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);
        
        $announcement = \App\Models\Announcement::findOrFail($id);
        $announcement->update(['is_active' => !$announcement->is_active]);
        
        $status = $announcement->is_active ? 'Activated' : 'Deactivated';
        
        // Audit Log
        \App\Utils\AuditLogger::log('Bulletins', 'Updated', "{$status} bulletin: {$announcement->title}.");
        
        return response()->json($announcement);
    }
}
