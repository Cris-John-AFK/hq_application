<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OmniSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q');
        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $isAdmin = in_array(Auth::user()->role, ['admin', 'hr']);

        $results = [];

        // 1. Search Employees / Users (Visible to Admins mostly)
        if ($isAdmin) {
            // Search master Employee list first
            $employees = Employee::with('department')
                ->where('first_name', 'ilike', "%{$q}%")
                ->orWhere('last_name', 'ilike', "%{$q}%")
                ->orWhere('employee_id', 'ilike', "%{$q}%")
                ->limit(5)
                ->get();

            foreach ($employees as $emp) {
                $results[] = [
                    'id' => $emp->id, // Internal DB ID
                    'employee_code' => $emp->employee_id, // Code for display
                    'type' => 'Employee',
                    'title' => $emp->name,
                    'subtitle' => $emp->employee_id . ' • ' . ($emp->department->name ?? 'No Department'),
                    'avatar' => $emp->avatar ? Storage::url($emp->avatar) : null,
                    'initials' => $emp->initials,
                ];
            }

            // Also search Users if they aren't employees (like system-only admins)
            $users = User::with('department')
                ->where('name', 'ilike', "%{$q}%")
                ->whereNotIn('id_number', $employees->pluck('employee_id'))
                ->limit(3)
                ->get();

            foreach ($users as $user) {
                $results[] = [
                    'id' => $user->id,
                    'employee_code' => $user->id_number ?: 'SYSTEM',
                    'type' => 'Employee',
                    'title' => $user->name,
                    'subtitle' => ($user->id_number ?: 'System') . ' • ' . ($user->department->name ?? $user->getAttribute('department') ?? 'No Department'),
                    'avatar' => $user->avatar_url,
                    'initials' => collect(explode(' ', $user->name))->map(fn($n) => mb_substr($n, 0, 1))->join(''),
                ];
            }
        }

        // 2. Search Leave Requests (By ID, Type, or User/Employee Name)
        $leavesQuery = LeaveRequest::with(['user', 'employee.department'])
            ->where(function ($query) use ($q) {
                $query->where('leave_type', 'ilike', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'ilike', "%{$q}%");
                    })
                    ->orWhereHas('employee', function ($eq) use ($q) {
                        $eq->where('first_name', 'ilike', "%{$q}%")
                            ->orWhere('last_name', 'ilike', "%{$q}%");
                    });

                // Add numeric ID search if applicable
                if (is_numeric($q)) {
                    $query->orWhere('id', $q);
                } elseif (str_starts_with(strtoupper($q), 'RID-')) {
                    $numericPart = substr($q, 4);
                    if (is_numeric($numericPart)) {
                        $query->orWhere('id', (int) $numericPart);
                    }
                }
            });

        if (!$isAdmin) {
            $leavesQuery->where('user_id', Auth::id());
        }

        $leaves = $leavesQuery->latest()->limit(5)->get();

        foreach ($leaves as $leaf) {
            $name = $leaf->user->name ?? ($leaf->employee ? $leaf->employee->first_name . ' ' . $leaf->employee->last_name : 'Unknown');
            $results[] = [
                'id' => $leaf->id,
                'type' => 'Request',
                'title' => "{$leaf->leave_type} - {$name}",
                'subtitle' => "RID-" . str_pad($leaf->id, 6, '0', STR_PAD_LEFT) . " • " . $leaf->from_date,
                'status' => $leaf->status,
            ];
        }

        return response()->json($results);
    }
}
