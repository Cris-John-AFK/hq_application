<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;

class ComplianceService
{
    /**
     * validateRule
     * Returns 'passed' => true/false, 'message' => ...
     */
    public function validateRule($user, string $leaveType, float $days)
    {
        // $user can be \App\Models\User or \App\Models\Employee
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
        // Employee model might have these in details
        $details = $user->details ?? $user; 
        $isSoloParent = $user->is_solo_parent ?? false; 

        if (!$isSoloParent) {
            return [
                'passed' => false,
                'message' => 'User is not tagged as a Solo Parent.'
            ];
        }
        
        $query = LeaveRequest::where('leave_type', 'Solo Parent')
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year);

        if (isset($user->id) && $user instanceof \App\Models\User) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('employee_id', $user->id);
        }

        $used = $query->sum('days_taken');
            
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
        $gender = $user->gender ?? $user->details?->gender;

        if ($gender !== 'Female') {
             return [
                'passed' => false,
                'message' => 'VAWS Leave is applicable only for female employees.'
            ];
        }

        $query = LeaveRequest::where('leave_type', 'VAWS')
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year);

        if (isset($user->id) && $user instanceof \App\Models\User) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('employee_id', $user->id);
        }

        $used = $query->sum('days_taken');

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
        $gender = $user->gender ?? $user->details?->gender;
        if ($gender !== 'Female') {
             return [
                'passed' => false,
                'message' => 'Maternity Leave is applicable only for female employees.'
            ];
        }
        return ['passed' => true];
    }

    private function checkPaternity($user)
    {
        $gender = $user->gender ?? $user->details?->gender;
        $civilStatus = $user->civil_status ?? $user->details?->civil_status;

        if ($gender !== 'Male') {
             return [
                'passed' => false,
                'message' => 'Paternity Leave is applicable only for male employees.'
            ];
        }
        if ($civilStatus !== 'Married') {
             return [
                'passed' => false,
                'message' => 'Paternity Leave usually requires being married (Labor Code).'
            ];
        }
        return ['passed' => true];
    }

    private function checkSIL($user, $days)
    {
        $credits = $user->leave_credits ?? $user->sil_credits ?? 0;
        if (($credits - $days) < 0) {
            return [
                'passed' => false,
                'message' => "Insufficient SIL credits. Available: {$credits}, Requested: {$days}."
            ];
        }
        return ['passed' => true];
    }
}
