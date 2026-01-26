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
    public function detectPatterns(User $user)
    {
        $flags = [];
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'Approved')
            ->orderBy('from_date', 'desc')
            ->get();

        if ($leaves->isEmpty()) {
            return [];
        }

        // 1. Frequent Fri/Mon Leaver
        if ($this->isFrequentLongWeekender($leaves)) {
            $flags[] = [
                'type' => 'pattern',
                'severity' => 'medium',
                'label' => 'Long Weekend Seeker',
                'description' => 'Frequently takes leaves on Fridays or Mondays.'
            ];
        }

        // 2. Heavy Usage Warning (e.g., > 3 leaves in last 30 days)
        if ($this->isHeavyUser($leaves)) {
            $flags[] = [
                'type' => 'velocity',
                'severity' => 'high',
                'label' => 'Heavy Recent Usage',
                'description' => 'Has taken more than 3 days off in the last 30 days.'
            ];
        }
        
        // 3. Pattern: Sick Leave always on same day? (Basic check)
        // This is simplified; real enterprise systems use more complex stats.

        return $flags;
    }

    private function isFrequentLongWeekender($leaves)
    {
        $fridayMondayCount = 0;
        $totalCheck = min($leaves->count(), 10); // Check last 10 leaves
        
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
        $recentLeaves = $leaves->filter(function($leave) use ($thirtyDaysAgo) {
             return Carbon::parse($leave->from_date)->gt($thirtyDaysAgo);
        });

        // Sum up days taken
        $totalDays = $recentLeaves->sum('days_taken');

        return $totalDays > 3;
    }
}
