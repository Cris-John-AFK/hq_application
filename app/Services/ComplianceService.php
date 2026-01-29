<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;

class ComplianceService
{
    /**
     * validateCompliance
     * Returns 'passed' => true/false, 'message' => ...
     */
    public function validateRule(User $user, string $leaveType, float $days)
    {
        switch (strtoupper($leaveType)) {
            case 'SIL':
                return $this->checkSIL($user, $days);
            case 'SOLO PARENT':
                return $this->checkSoloParent($user, $days);
            case 'VAWS':
                return $this->checkVAWS($user, $days);
            case 'MATERNITY':
                return $this->checkMaternity($user);
            case 'PATERNITY':
                return $this->checkPaternity($user);
            default:
                return ['passed' => true];
        }
    }

    private function checkSoloParent($user, $days)
    {
        if (!$user->is_solo_parent) {
            return [
                'passed' => false,
                'message' => 'User is not tagged as a Solo Parent.'
            ];
        }
        
        // Legal limit usually 7 days per year, renewable
        // We need to check usage this year
        $used = LeaveRequest::where('user_id', $user->id)
            ->where('leave_type', 'Solo Parent')
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year)
            ->sum('days_taken');
            
        if (($used + $days) > 7) {
            return [
                'passed' => false,
                'message' => "Exceeds annual allocation of 7 days. Used: {$used}, Requested: {$days}."
            ];
        }

        return ['passed' => true];
    }

    private function checkVAWS($user, $days)
    {
        if ($user->gender !== 'Female') {
             return [
                'passed' => false,
                'message' => 'VAWS Leave is applicable only for female employees.'
            ];
        }

        // Limit 10 days
        $used = LeaveRequest::where('user_id', $user->id)
            ->where('leave_type', 'VAWS')
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year)
            ->sum('days_taken');

        if (($used + $days) > 10) {
             return [
                'passed' => false,
                'message' => "Exceeds annual allocation of 10 days. Used: {$used}."
            ];
        }

        return ['passed' => true];
    }
    
    private function checkMaternity($user)
    {
        if ($user->gender !== 'Female') {
             return [
                'passed' => false,
                'message' => 'Maternity Leave is applicable only for female employees.'
            ];
        }
        return ['passed' => true];
    }

    private function checkPaternity($user)
    {
        if ($user->gender !== 'Male') {
             return [
                'passed' => false,
                'message' => 'Paternity Leave is applicable only for male employees.'
            ];
        }
        if ($user->civil_status !== 'Married') {
             // Technically Paternity is for married, but some companies allow common-law. Strict adherence for now.
             return [
                'passed' => false,
                'message' => 'Paternity Leave usually requires being married (Labor Code).'
            ];
        }
        return ['passed' => true];
    }

    private function checkSIL($user, $days)
    {
        if (($user->leave_credits - $days) < 0) {
            return [
                'passed' => false,
                'message' => "Insufficient SIL credits. Available: {$user->leave_credits}, Requested: {$days}."
            ];
        }
        return ['passed' => true];
    }
}
