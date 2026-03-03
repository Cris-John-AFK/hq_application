<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceRecord;

return new class extends Migration {
    protected function mergeInto($targetName, $sourceNames)
    {
        // 1. Ensure Target exists
        $target = Department::where('name', $targetName)->first();
        if (!$target) {
            // Pick the first available source to become target
            $target = Department::whereIn('name', $sourceNames)->first();
            if ($target) {
                $target->update(['name' => $targetName]);
            } else {
                // If neither exists, create target if there's data for it
                $target = Department::create(['name' => $targetName]);
            }
        } else {
            // Already exists, just ensure it's exactly targetName
            $target->update(['name' => $targetName]);
        }

        // 2. Merge others into Target
        $others = Department::whereIn('name', $sourceNames)
            ->where('id', '!=', $target->id)
            ->get();

        foreach ($others as $other) {
            Employee::where('department_id', $other->id)->update(['department_id' => $target->id]);
            $other->delete();
        }

        return $target;
    }

    public function up()
    {
        // Maintenance
        $this->mergeInto('Maintenance and Engineering', [
            'Maintenance And Engineering',
            'Maintenance and Engineering',
            'Maintenance & Engineering'
        ]);

        // Production
        $this->mergeInto('Production', [
            'Production',
            'Production Dept.',
            'Production Support Group'
        ]);

        // General
        $this->mergeInto('General Admin Support', [
            'General Admin Support',
            'General'
        ]);

        // Global Sync
        $employees = Employee::with('department')->get();
        foreach ($employees as $employee) {
            if ($employee->department) {
                $deptName = $employee->department->name;

                // Update Attendance Records
                AttendanceRecord::where('employee_id_number', $employee->employee_id)
                    ->update(['department' => $deptName]);

                AttendanceRecord::where('employee_name', 'IREGEXP', '^' . preg_quote($employee->first_name . ' ' . $employee->last_name) . '$')
                    ->update(['department' => $deptName]);
            }
        }

        // Final N/A fix
        AttendanceRecord::where('department', 'N/A')->orWhereNull('department')
            ->each(function ($r) {
                $match = Employee::where('employee_id', $r->employee_id_number)->first();
                if (!$match) {
                    $match = Employee::whereRaw("LOWER(CONCAT(first_name, ' ', last_name)) = ?", [strtolower($r->employee_name)])->first();
                }
                if ($match && $match->department) {
                    $r->update(['department' => $match->department->name]);
                }
            });
    }

    public function down()
    {
    }
};
