<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class CreditForecastingService
{
    /**
     * Forecast remaining credits by year end based on usage patterns.
     */
    public function forecast($user)
    {
        // 1. Current Balance
        $currentCredits = $user->leave_credits ?? $user->sil_credits ?? 0;

        // Base Query
        $q = LeaveRequest::whereYear('from_date', now()->year);
        if ($user instanceof \App\Models\User) {
            $q->where('user_id', $user->id);
        } else {
            $q->where('employee_id', $user->id);
        }

        // 2. Scheduled (Approved Future) Leaves
        $futureApproved = (clone $q)->where('status', 'Approved')
            ->where('from_date', '>', now())
            ->sum('days_taken');

        // 3. Pending Leaves (Potential deduction)
        $pending = (clone $q)->where('status', 'Pending')
            ->sum('days_taken');

        // 4. Burn Rate (Average credits used per month this year)
        $leavesThisYear = (clone $q)->where('status', 'Approved')
            ->sum('days_taken');
            
        $monthsPassed = now()->month;
        $burnRate = $monthsPassed > 0 ? ($leavesThisYear / $monthsPassed) : 0;
        
        $monthsRemaining = 12 - $monthsPassed;
        $projectedUsage = $burnRate * $monthsRemaining;

        return [
            'current_credits' => $currentCredits,
            'scheduled_usage' => $futureApproved,
            'pending_usage' => $pending,
            'guaranteed_balance' => $currentCredits - $futureApproved,
            'projected_balance' => $currentCredits - $futureApproved - $projectedUsage,
            'burn_rate' => round($burnRate, 2), // days per month
            'message' => "Avg usage: " . round($burnRate, 2) . " days/mo. likely to end year with " . round($currentCredits - $futureApproved - $projectedUsage, 1) . " credits."
        ];
    }
}
