<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return response()->json(Department::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name|max:255',
        ]);

        $department = Department::create($validated);

        // Audit Log
        \App\Utils\AuditLogger::log('Settings', 'Created', "Added new department: {$department->name}.");

        return response()->json($department, 201);
    }

    public function getStats()
    {
        $departments = Department::withCount(['users'])->orderBy('name')->get();
        
        $stats = $departments->map(function($dept) {
            $headcount = $dept->users_count;
            if ($headcount === 0) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'headcount' => 0,
                    'avg_present' => 0,
                    'avg_absent' => 0,
                    'avg_on_leave' => 0,
                    'rate' => '0%'
                ];
            }

            // Simulate Previous Week (5 working days)
            $totalPossibleDays = $headcount * 5;
            $actualPresentDays = rand(round($totalPossibleDays * 0.85), $totalPossibleDays);
            $totalAbsentDays = $totalPossibleDays - $actualPresentDays;
            
            $avgPresent = round($actualPresentDays / 5, 1);
            $avgAbsent = round($totalAbsentDays / 5, 1);
            $avgOnLeave = round(rand(0, 5) / 5, 1);
            
            $rate = ($totalPossibleDays > 0) ? round(($actualPresentDays / $totalPossibleDays) * 100, 1) : 0;

            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'headcount' => $headcount,
                'avg_present' => $avgPresent,
                'avg_absent' => $avgAbsent,
                'avg_on_leave' => $avgOnLeave,
                'rate' => $rate . '%'
            ];
        });

        return response()->json($stats);
    }
}
