<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    private function verifyEmployee($employee_id, $birthdate)
    {
        $employee = Employee::with('details')->where('employee_id', $employee_id)->first();

        // Fallback for ID if submitting using employee table PK (submitLeave)
        if (!$employee) {
            $employee = Employee::with('details')->find($employee_id);
        }

        if (!$employee || !$employee->details) {
            return null;
        }

        $dbDob = $employee->details->birthdate; // e.g., '1995-05-10'

        // Parse exactly 8 digits representing either MMDDYYYY or DDMMYYYY
        $part1 = substr($birthdate, 0, 2);
        $part2 = substr($birthdate, 2, 2);
        $year = substr($birthdate, 4, 4);

        // Check both possible permutations
        $match1 = "$year-$part1-$part2" === $dbDob; // MMDDYYYY
        $match2 = "$year-$part2-$part1" === $dbDob; // DDMMYYYY

        if (!$match1 && !$match2) {
            return null;
        }

        return $employee;
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'birthdate' => 'required|string|size:8'
        ]);

        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            return response()->json(['message' => 'Invalid ID number or birthdate.'], 401);
        }

        // Get leave history excluding archived
        $leaveHistory = LeaveRequest::where('employee_id', $employee->id)
            ->where('is_archived', false)
            ->latest('created_at')
            ->get();

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
            'additional_details' => 'nullable|array'
        ]);

        // Re-authenticate silently utilizing internal primary key id if fallback needed
        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            return response()->json(['message' => 'Unauthorized submission.'], 401);
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'user_id' => null, // Filled by employee portal
            'date_filed' => now()->toDateString(),
            'leave_type' => $request->leave_type,
            'category' => null, // Explicitly excluded from employee view
            'request_type' => $request->request_type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days_taken' => $request->days_taken,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'Pending',
            'is_paid' => false,
            'additional_details' => $request->additional_details ?? []
        ]);

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
            'additional_details' => 'nullable|array'
        ]);

        $employee = $this->verifyEmployee($request->employee_id, $request->birthdate);

        if (!$employee) {
            return response()->json(['message' => 'Unauthorized submission.'], 401);
        }

        $leave = LeaveRequest::where('employee_id', $employee->id)->findOrFail($id);

        if ($leave->status !== 'Pending') {
            return response()->json(['message' => 'Only pending leave requests can be edited.'], 403);
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
            'additional_details' => $request->additional_details ?? []
        ]);

        return response()->json(['message' => 'Leave request updated successfully.', 'leave' => $leave], 200);
    }
}
