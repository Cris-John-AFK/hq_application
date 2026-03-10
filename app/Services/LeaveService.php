<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use App\Utils\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeaveService
{
    /**
     * Process a single leave request action (Approve/Reject/Cancel)
     */
    public function processAction(LeaveRequest $request, array $data, User $actor)
    {
        return DB::transaction(function () use ($request, $data, $actor) {
            $oldStatus = $request->status;
            $newStatus = $data['status'];
            $remarks = $data['remarks'] ?? '';

            // 1. Update status and metadata
            $request->status = $newStatus;

            if ($newStatus === 'Approved') {
                $request->hr_approved_at = now();
                $request->hr_approver_id = $actor->id;

                // Deduct credits if it's the final approval
                $this->adjustCredits($request, 'deduct');
            } elseif ($newStatus === 'Rejected') {
                $request->admin_remarks = $remarks;
            }

            $request->save();

            // 2. Audit Log
            AuditLogger::log(
                'Leave Management',
                "Changed Request #{$request->id} status from {$oldStatus} to {$newStatus}",
                $request->toArray(),
                ['old_status' => $oldStatus, 'remarks' => $remarks]
            );

            // 3. Notification
            $this->sendNotification($request, $newStatus, $remarks);

            return $request;
        });
    }

    /**
     * Deduct or Refund credits based on action
     */
    public function adjustCredits(LeaveRequest $request, $action = 'deduct')
    {
        $user = $request->user;
        if (!$user)
            return;

        $leaveType = strtolower(str_replace(' ', '_', $request->leave_type));
        $days = $request->days_taken ?? 0;

        if ($days <= 0)
            return;

        if ($action === 'deduct') {
            $user->decrement($leaveType, $days);
        } else {
            $user->increment($leaveType, $days);
        }
    }

    protected function sendNotification(LeaveRequest $request, $status, $remarks)
    {
        $user = $request->user;
        if (!$user)
            return;

        Notification::create([
            'user_id' => $user->id,
            'title' => "Leave Request #{$request->id} {$status}",
            'message' => "Your {$request->leave_type} request for {$request->from_date} has been {$status}. " . ($remarks ? "Remarks: {$remarks}" : ""),
            'type' => $status === 'Approved' ? 'success' : ($status === 'Rejected' ? 'warning' : 'info'),
            'is_read' => false
        ]);
    }
}
