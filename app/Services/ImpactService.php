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
    public function checkImpact(User $user, $fromDate, $toDate)
    {
        $department = $user->department;
        if (!$department) {
            return null; // No department, no impact analysis possible
        }

        // 1. Total people in department
        $totalMembers = User::where('department', $department)->count();
        
        if ($totalMembers === 0) return null;

        // 2. Find others on leave in this range
        $othersOnLeave = LeaveRequest::whereHas('user', function($q) use ($department, $user) {
                $q->where('department', $department)
                  ->where('id', '!=', $user->id);
            })
            ->where('status', 'Approved')
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                      ->orWhereBetween('to_date', [$fromDate, $toDate])
                      ->orWhere(function ($q) use ($fromDate, $toDate) {
                          $q->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                      });
            })
            ->with('user:id,name,position')
            ->get();

        $percentage = (($othersOnLeave->count() + 1) / $totalMembers) * 100;

        return [
            'department' => $department,
            'total_members' => $totalMembers,
            'others_on_leave_count' => $othersOnLeave->count(),
            'others_on_leave_details' => $othersOnLeave->pluck('user.name')->toArray(),
            'absent_percentage' => round($percentage, 1),
            'severity' => $percentage > 30 ? 'high' : ($percentage > 10 ? 'medium' : 'low'),
            'message' => $percentage > 30 
                ? "Critical: {$percentage}% of {$department} will be absent." 
                : "Standard: {$othersOnLeave->count()} other(s) on leave."
        ];
    }
}
