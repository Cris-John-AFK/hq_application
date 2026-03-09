<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\Department;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'details', 'user']);

        // Default: only show non-archived
        if ($request->boolean('archived', false)) {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'ilike', "%{$search}%")
                    ->orWhere('first_name', 'ilike', "%{$search}%")
                    ->orWhere('employee_id', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('working_hours')) {
            $query->where('working_hours', 'like', "%{$request->working_hours}%");
        }

        // Filter based on specific leave types (having balance > 0)
        if ($request->filled('has_balance')) {
            $type = $request->has_balance; // e.g. 'vl', 'pl', 'sp', 'bl', 'vawc'
            $columnMap = [
                'vl' => 'vacation_leave',
                'pl' => 'paternity_leave',
                'sp' => 'solo_parent_leave',
                'bl' => 'bereavement_leave',
                'vawc' => 'vawc_leave'
            ];
            $col = $columnMap[$type] ?? null;
            if ($col) {
                $query->where($col, '>', 0);
            }
        }

        // Check if user wants ALL records (no pagination)
        if ($request->boolean('all', false)) {
            return response()->json($query->latest($request->boolean('archived', false) ? 'archived_at' : 'created_at')->get());
        }

        return response()->json($query->latest($request->boolean('archived', false) ? 'archived_at' : 'created_at')->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Core
            'employee_id' => 'required|unique:employees,employee_id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string',
            'employment_status' => 'required|string',
            'date_hired' => 'required|date',
            'email' => 'nullable|email',
            'leave_credits' => 'nullable|numeric',

            // Details
            'birthdate' => 'required|date',
            'gender' => 'required|string',
            'civil_status' => 'nullable|string',
            'sss_number' => 'nullable|string',
            'philhealth_number' => 'nullable|string',
            'pagibig_number' => 'nullable|string',
            'tin_number' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Handle optional avatar
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            $employee = Employee::create([
                'employee_id' => $validated['employee_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'department_id' => $validated['department_id'],
                'position' => $validated['position'],
                'employment_status' => $validated['employment_status'],
                'date_hired' => $validated['date_hired'],
                'email' => $validated['email'] ?? null,
                'vacation_leave' => $validated['vacation_leave'] ?? 0,
                'avatar' => $avatarPath ? '/storage/' . $avatarPath : null,
            ]);

            $employee->details()->create([
                'birthdate' => $validated['birthdate'],
                'gender' => $validated['gender'],
                'civil_status' => $validated['civil_status'] ?? null,
                'sss_number' => $validated['sss_number'] ?? null,
                'philhealth_number' => $validated['philhealth_number'] ?? null,
                'pagibig_number' => $validated['pagibig_number'] ?? null,
                'tin_number' => $validated['tin_number'] ?? null,
            ]);

            // Post-action cleanup
            Department::cleanup();
            AttendanceRecord::syncDepartments();
            \Illuminate\Support\Facades\Cache::flush();

            return response()->json($employee->load('details', 'department'), 201);
        });
    }

    public function show($id)
    {
        return response()->json(Employee::with('details', 'department', 'user')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $oldData = $employee->load('details', 'department')->toArray();

        $validated = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'department_id' => 'sometimes|exists:departments,id',
            'position' => 'sometimes|string',
        ]);

        return DB::transaction(function () use ($request, $employee, $oldData) {
            $employee->update($request->only([
                'first_name',
                'last_name',
                'middle_name',
                'department_id',
                'position',
                'employment_status',
                'date_hired',
                'email',
                'vacation_leave',
                'sick_leave',
                'paternity_leave',
                'solo_parent_leave',
                'bereavement_leave',
                'vawc_leave',
                'working_hours'
            ]));

            if ($request->has('details')) {
                $employee->details()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    $request->input('details')
                );
            }

            // Sync User role if provided
            if ($request->has('role') && $employee->user) {
                $employee->user->update(['role' => $request->role]);
                \App\Utils\AuditLogger::log('Security', 'Updated', "Changed system role for {$employee->name} to {$request->role}.", null, ['role' => $request->role]);
            }

            $employee->refresh();
            $newData = $employee->load('details', 'department')->toArray();

            // Post-action cleanup
            Department::cleanup();
            AttendanceRecord::syncDepartments();
            \Illuminate\Support\Facades\Cache::flush();

            return response()->json($employee);
        });
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $empName = $employee->name;
        $empId = $employee->employee_id;

        // Use archiving instead of soft-deleting if requested by user pattern
        $employee->update([
            'is_archived' => true,
            'archived_at' => now()
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('EMPLOYEES', 'ARCHIVED', "Archived employee {$empName} (#{$empId}).", null, $employee->toArray());

        // Post-action cleanup
        Department::cleanup();
        AttendanceRecord::syncDepartments();
        \Illuminate\Support\Facades\Cache::flush();

        return response()->json(['message' => 'Employee moved to archive']);
    }

    public function archive($id)
    {
        return $this->destroy($id);
    }

    public function unarchive($id)
    {
        $employee = Employee::findOrFail($id);
        $empName = $employee->name;
        $empId = $employee->employee_id;

        $employee->update([
            'is_archived' => false,
            'archived_at' => null
        ]);

        // Audit Log
        \App\Utils\AuditLogger::log('EMPLOYEES', 'RESTORED', "Restored employee {$empName} (#{$empId}) from archive.", null, $employee->toArray());

        // Post-action cleanup
        Department::cleanup();
        AttendanceRecord::syncDepartments();
        \Illuminate\Support\Facades\Cache::flush();

        return response()->json(['message' => 'Employee restored from archive']);
    }

    public function findByEmployeeId($id)
    {
        $employee = Employee::with(['details', 'department'])
            ->where(function ($q) use ($id) {
                $q->where('employee_id', $id)
                    ->orWhere('first_name', 'ilike', "%{$id}%")
                    ->orWhere('last_name', 'ilike', "%{$id}%");
            })
            ->where('is_archived', false)
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\EmployeesImport, $request->file('file'));

            // Audit Log
            \App\Utils\AuditLogger::log('MASTERLIST', 'IMPORTED', "Imported employees via Excel file.");

            \Illuminate\Support\Facades\Cache::flush();
            return response()->json(['message' => 'Import successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 422);
        }
    }

    public function adjustLeave(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'hr') {
            return response()->json(['message' => 'Unauthorized. Only HR and Admins can adjust leave balances.'], 403);
        }

        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'leave_type' => 'required|string|in:vacation_leave,sick_leave,paternity_leave,solo_parent_leave,bereavement_leave,vawc_leave',
            'action' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:0.5',
            'justification' => 'required|string|min:5'
        ]);

        $field = $validated['leave_type'];
        $amount = (float) $validated['amount'];
        $oldBalance = $employee->$field ?? 0;

        if ($validated['action'] === 'deduct') {
            if ($oldBalance < $amount) {
                return response()->json(['message' => 'Cannot deduct more than the current balance.'], 422);
            }
            $employee->decrement($field, $amount);
        } else {
            $employee->increment($field, $amount);
        }

        $employee->refresh();
        $newBalance = $employee->$field;

        // Log the action explicitly to system logs so that there is an Audit Trail
        \App\Utils\AuditLogger::log(
            'Employees',
            'Leave Adjusted',
            Auth::user()->name . " {$validated['action']}ed {$amount} to {$employee->name}'s {$field}. (Old: {$oldBalance}, New: {$newBalance})",
            ['old_balance' => $oldBalance, 'leave_type' => $field],
            ['new_balance' => $newBalance, 'justification' => $validated['justification']]
        );

        return response()->json([
            'message' => 'Leave balance updated successfully.',
            'employee' => $employee
        ]);
    }

    public function getShiftStats()
    {
        $shifts = [
            'A' => '7:00 AM - 3:00 PM',
            'B' => '7:00 PM - 4:00 AM',
            'C' => '6:00 AM - 2:00 PM',
            'D' => '2:00 PM - 10:00 PM',
            'E' => '8:00 AM - 4:00 PM'
        ];

        $stats = [];
        foreach ($shifts as $code => $time) {
            $stats[] = [
                'code' => $code,
                'time' => $time,
                'count' => Employee::where('working_hours', 'like', "%{$time}%")->where('is_archived', false)->count()
            ];
        }

        return response()->json($stats);
    }

    public function bulkUpdateLeaves(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'leave_type' => 'required|string|in:vacation_leave,sick_leave,paternity_leave,solo_parent_leave,bereavement_leave,vawc_leave',
            'amount' => 'required|numeric',
            'mode' => 'required|in:set,add',
            'reason' => 'nullable|string'
        ]);

        $ids = $validated['employee_ids'];
        $field = $validated['leave_type'];
        $amount = (float) $validated['amount'];
        $mode = $validated['mode'];
        $reason = $validated['reason'] ?? 'Bulk credit update';

        return DB::transaction(function () use ($ids, $field, $amount, $mode, $reason) {
            $employees = Employee::whereIn('id', $ids)->get();
            $count = 0;

            foreach ($employees as $employee) {
                $oldValue = $employee->$field;
                if ($mode === 'set') {
                    $employee->$field = $amount;
                } else {
                    $employee->$field += $amount;
                }
                $employee->save();

                \App\Utils\AuditLogger::log('Leaves', 'Bulk Adjust', "Bulk updated {$field} for {$employee->name} from {$oldValue} to {$employee->$field}. Reason: {$reason}");
                $count++;
            }

            \Illuminate\Support\Facades\Cache::flush();
            return response()->json(['message' => "Successfully updated credits for {$count} employees.", 'updated_count' => $count]);
        });
    }
}
