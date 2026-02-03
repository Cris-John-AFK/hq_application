<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'details']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'ilike', "%{$search}%")
                  ->orWhere('first_name', 'ilike', "%{$search}%")
                  ->orWhere('employee_id', 'ilike', "%{$search}%");
            });
        }
        
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        if ($request->filled('gender')) {
            $query->whereHas('details', function($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }

        if ($request->filled('civil_status')) {
            $query->whereHas('details', function($q) use ($request) {
                $q->where('civil_status', $request->civil_status);
            });
        }

        if ($request->filled('hired_year')) {
            $query->whereYear('date_hired', $request->hired_year);
        }

        if ($request->filled('missing_id')) {
            $query->whereHas('details', function($q) use ($request) {
                $type = $request->missing_id;
                if ($type === 'sss') $q->whereNull('sss_number')->orWhere('sss_number', '');
                if ($type === 'philhealth') $q->whereNull('philhealth_number')->orWhere('philhealth_number', '');
                if ($type === 'pagibig') $q->whereNull('pagibig_number')->orWhere('pagibig_number', '');
                if ($type === 'tin') $q->whereNull('tin_number')->orWhere('tin_number', '');
            });
        }

        return response()->json($query->latest()->paginate(10));
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
                'leave_credits' => $validated['leave_credits'] ?? 0,
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

            // Audit Log
            \App\Utils\AuditLogger::log('Masterlist', 'Created', "Manually added a new employee: {$employee->name} ({$employee->employee_id}).", null, $employee->toArray());

            return response()->json($employee->load('details', 'department'), 201);
        });
    }

    public function show($id)
    {
        return response()->json(Employee::with('details', 'department')->findOrFail($id));
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
                'first_name', 'last_name', 'middle_name', 'department_id',
                'position', 'employment_status', 'date_hired', 'email', 'leave_credits'
            ]));

            if ($request->has('details')) {
                $employee->details()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    $request->input('details')
                );
            }

            $employee->refresh();
            $newData = $employee->load('details', 'department')->toArray();

            // Audit Log
            \App\Utils\AuditLogger::log('Masterlist', 'Updated', "Updated profile/details of employee: {$employee->name} ({$employee->employee_id}).", $oldData, $newData);

            return response()->json($employee);
        });
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $empName = $employee->name;
        $empId = $employee->employee_id;
        $employee->delete();

        // Audit Log
        \App\Utils\AuditLogger::log('Masterlist', 'Deleted', "Removed employee from masterlist: {$empName} ({$empId}).");

        return response()->json(['message' => 'Employee deleted']);
    }

    public function findByEmployeeId($id)
    {
        $employee = Employee::with(['details', 'department'])->where('employee_id', $id)->firstOrFail();
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
            \App\Utils\AuditLogger::log('Masterlist', 'Imported', "Imported employees via Excel file.");

            return response()->json(['message' => 'Import successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 422);
        }
    }
}
