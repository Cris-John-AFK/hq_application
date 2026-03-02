<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeavePatternService
{
    /**
     * Detect patterns in leave usage.
     * Returns an array of flags with details.
     */
    public function detectPatterns($user)
    {
        $flags = [];
        $query = LeaveRequest::where('status', 'Approved')
            ->orderBy('from_date', 'desc');

        if (isset($user->id) && $user instanceof \App\Models\User) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('employee_id', $user->id);
        }

        $leaves = $query->get();

        if ($leaves->isEmpty() || $this->isHighlyReliable($leaves)) {
            $flags[] = [
                'type' => 'positive',
                'severity' => 'low',
                'label' => 'Extensive Reliability Period',
                'description' => 'Employee has not requested any form of absence within the last 90 days.',
                'icon' => 'pi-shield'
            ];

            if ($leaves->isEmpty())
                return $flags;
        }

        // 1. Frequent Fri/Mon Leaver
        if ($this->isFrequentLongWeekender($leaves)) {
            $flags[] = [
                'type' => 'pattern',
                'severity' => 'medium',
                'label' => 'Long Weekend Proclivity',
                'description' => 'Over 40% of recent approved absences fall directly on a Friday or Monday, extending the regular weekend.',
                'icon' => 'pi-calendar-plus'
            ];
        }

        // 2. Heavy Usage Warning (e.g., > 3 leaves in last 30 days)
        if ($this->isHeavyUser($leaves)) {
            $flags[] = [
                'type' => 'velocity',
                'severity' => 'high',
                'label' => 'Elevated Absence Velocity',
                'description' => 'Employee has exhausted more than 3 leave days within the last 30-day moving window. Consider checking for potential burnout.',
                'icon' => 'pi-chart-line'
            ];
        }

        return $flags;
    }

    private function isHighlyReliable($leaves)
    {
        if ($leaves->isEmpty())
            return true;

        $lastLeave = Carbon::parse($leaves->first()->from_date);
        return $lastLeave->lt(Carbon::now()->subDays(90));
    }

    private function isFrequentLongWeekender($leaves)
    {
        $fridayMondayCount = 0;
        $totalCheck = min($leaves->count(), 10); // Check last 10 leaves
        if ($totalCheck === 0)
            return false;

        foreach ($leaves->take(10) as $leave) {
            $date = Carbon::parse($leave->from_date);
            if ($date->isFriday() || $date->isMonday()) {
                $fridayMondayCount++;
            }
        }

        // If > 40% of last 10 leaves are Fri/Mon
        return ($fridayMondayCount / $totalCheck) >= 0.4;
    }

    private function isHeavyUser($leaves)
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $recentLeaves = $leaves->filter(function ($leave) use ($thirtyDaysAgo) {
            return Carbon::parse($leave->from_date)->gt($thirtyDaysAgo);
        });

        // Sum up days taken
        $totalDays = $recentLeaves->sum('days_taken');

        return $totalDays > 3;
    }
}
