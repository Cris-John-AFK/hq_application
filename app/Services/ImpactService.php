<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class ImpactService
{
    /**
     * Check how a leave request impacts the department.
     */
    public function checkImpact($user, $fromDate, $toDate)
    {
        $department = null;
        if ($user instanceof \App\Models\User) {
            $department = $user->department;
        } else {
            $department = $user->department?->name;
        }

        if (!$department) {
            return null; // No department, no impact analysis possible
        }

        // 1. Total people in department (Masterlist + Users)
        $totalMembers = \App\Models\Employee::whereHas('department', function($q) use ($department) {
            $q->where('name', $department);
        })->count();
        
        if ($totalMembers === 0) return null;

        // 2. Find others on leave in this range
        $othersOnLeave = LeaveRequest::where('status', 'Approved')
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                      ->orWhereBetween('to_date', [$fromDate, $toDate])
                      ->orWhere(function ($q) use ($fromDate, $toDate) {
                          $q->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                      });
            })
            ->where(function($q) use ($user, $department) {
                // Filter by same department
                $q->whereHas('user', function($uq) use ($department, $user) {
                    $uq->where('department', $department);
                    if ($user instanceof \App\Models\User) $uq->where('id', '!=', $user->id);
                })->orWhereHas('employee.department', function($eq) use ($department, $user) {
                    $eq->where('name', $department);
                    if (!($user instanceof \App\Models\User)) $eq->where('id', '!=', $user->id);
                });
            })
            ->with(['user', 'employee'])
            ->get();

        $percentage = (($othersOnLeave->count() + 1) / $totalMembers) * 100;

        $othersDetails = $othersOnLeave->map(function($l) {
            return $l->user->name ?? ($l->employee->first_name . ' ' . $l->employee->last_name);
        })->toArray();
        return [
            'department' => $department,
            'total_members' => $totalMembers,
            'others_on_leave_count' => $othersOnLeave->count(),
            'others_on_leave_details' => $othersDetails,
            'absent_percentage' => round($percentage, 1),
            'severity' => $percentage > 30 ? 'high' : ($percentage > 10 ? 'medium' : 'low'),
            'message' => $percentage > 30 
                ? "Critical: " . round($percentage, 0) . "% of {$department} will be absent." 
                : "Standard: {$othersOnLeave->count()} other(s) on leave."
        ];
    }
}
