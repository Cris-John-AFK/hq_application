<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    private function verifyEmployee($id_input, $birthdate)
    {
        // Strictly use the human-readable employee_id column.
        // We ignore the internal primary key 'id' to prevent confusion 
        // with the masterlist row sequence number.
        $candidates = Employee::with('details')
            ->where('employee_id', $id_input)
            ->get();

        foreach ($candidates as $employee) {
            if (!$employee->details)
                continue;

            $dbDob = $employee->details->birthdate; // e.g., '1995-05-10'
            $dbDobStr = ($dbDob instanceof \Carbon\Carbon) ? $dbDob->toDateString() : (string) $dbDob;

            // Parse exactly 8 digits representing either MMDDYYYY or DDMMYYYY
            // Example: 06272003
            $part1 = substr($birthdate, 0, 2); // 06
            $part2 = substr($birthdate, 2, 2); // 27
            $year = substr($birthdate, 4, 4);  // 2003

            // Check both possible permutations (Month/Day vs Day/Month) 
            // to be flexible with user input regions.
            $match1 = "$year-$part1-$part2" === $dbDobStr; // MMDDYYYY -> YYYY-MM-DD
            $match2 = "$year-$part2-$part1" === $dbDobStr; // DDMMYYYY -> YYYY-MM-DD

            if ($match1 || $match2) {
                return $employee;
            }
        }

        return null;
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'birthdate' => 'required|string|size:8'
        ]);

        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            \App\Utils\AuditLogger::log('Authentication', 'Failed Login (Portal)', "Unauthorized portal access attempt for ID: {$request->employee_id}. Incorrect ID or PIN.");
            return response()->json(['message' => 'Invalid ID number or birthdate.'], 401);
        }

        // Get leave history excluding archived
        $leaveHistory = LeaveRequest::where('employee_id', $employee->id)
            ->where('is_archived', false)
            ->latest('created_at')
            ->get();

        // Audit Log
        \App\Utils\AuditLogger::log('Authentication', 'Login (Portal)', "Employee {$employee->name} (#{$employee->employee_id}) accessed the portal.");

        return response()->json([
            'employee' => $employee,
            'leave_history' => $leaveHistory
        ]);
    }

    public function submitLeave(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'birthdate' => 'required|string|size:8',

            'leave_type' => 'required|string',
            'request_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'days_taken' => 'required|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'reason' => 'required|string',
            'additional_details' => 'nullable',
            'attachment' => 'nullable|file|max:5120' // 5MB limit
        ]);

        // Re-authenticate silently utilizing internal primary key id if fallback needed
        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            return response()->json(['message' => 'Unauthorized submission.'], 401);
        }

        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave_attachments', 'public');
            $attachmentPath = '/storage/' . $path;
        }

        // Handle JSON decoded details from FormData
        $details = $request->additional_details;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'user_id' => null, // Filled by employee portal
            'date_filed' => $request->date_filed ?? now()->toDateString(),
            'leave_type' => $request->leave_type,
            'category' => null, // Explicitly excluded from employee view
            'request_type' => $request->request_type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days_taken' => $request->days_taken,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'attachment_path' => $attachmentPath,
            'status' => 'Pending',
            'is_paid' => false,
            'additional_details' => $details ?? []
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('Leaves', 'Created', "Employee {$employee->name} filed a new {$leave->leave_type} request for {$leave->days_taken} day(s).", null, $leave->toArray());

        return response()->json(['message' => 'Leave request submitted successfully.', 'leave' => $leave], 201);
    }

    public function updateLeave(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'birthdate' => 'required|string|size:8',

            'leave_type' => 'required|string',
            'request_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'days_taken' => 'required|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'reason' => 'required|string',
            'additional_details' => 'nullable',
            'attachment' => 'nullable|file|max:5120'
        ]);

        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            return response()->json(['message' => 'Unauthorized submission.'], 401);
        }

        $leave = LeaveRequest::where('employee_id', $employee->id)->findOrFail($id);
        $oldData = $leave->toArray();

        if ($leave->status !== 'Pending') {
            return response()->json(['message' => 'Only pending leave requests can be edited.'], 403);
        }

        // Handle attachment update
        $attachmentPath = $leave->attachment_path;
        if ($request->hasFile('attachment')) {
            // Delete old file if it exists
            if ($attachmentPath) {
                $oldPath = str_replace('/storage/', '', $attachmentPath);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('attachment')->store('leave_attachments', 'public');
            $attachmentPath = '/storage/' . $path;
        }

        // Handle JSON decoded details from FormData
        $details = $request->additional_details;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        $leave->update([
            'leave_type' => $request->leave_type,
            'request_type' => $request->request_type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days_taken' => $request->days_taken,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'attachment_path' => $attachmentPath,
            'additional_details' => $details ?? []
        ]);

        $newData = $leave->refresh()->toArray();
        // Audit Log
        \App\Utils\AuditLogger::log('Leaves', 'Updated', "Employee {$employee->name} updated their pending {$leave->leave_type} request.", $oldData, $newData);

        return response()->json(['message' => 'Leave request updated successfully.', 'leave' => $leave], 200);
    }

    public function archiveLeave(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'birthdate' => 'required|string|size:8',
        ]);

        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            return response()->json(['message' => 'Unauthorized submission.'], 401);
        }

        $leave = LeaveRequest::where('employee_id', $employee->id)->findOrFail($id);
        $oldData = $leave->toArray();

        $updates = [
            'is_archived' => true,
            'archived_at' => now()
        ];

        // If they archive a pending action, it should formally cancel it.
        // Doing this ensures HR doesn't keep a ghost pending item.
        $actionDesc = "archived";
        if ($leave->status === 'Pending') {
            $updates['status'] = 'Cancelled';
            $actionDesc = "cancelled & archived";
        }

        $leave->update($updates);
        $newData = $leave->refresh()->toArray();

        // Audit Log
        \App\Utils\AuditLogger::log('Leaves', 'Archived', "Employee {$employee->name} {$actionDesc} their {$leave->leave_type} request.", $oldData, $newData);

        return response()->json(['message' => 'Leave request archived successfully.', 'leave' => $leave], 200);
    }
}
