<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id_number',
        'employee_name',
        'date',
        'time_in',
        'time_out',
        'hours_worked',
        'status',
        'department'
    ];

    /**
     * Synchronize department names in attendance records with the current employee masterlist.
     */
    public static function syncDepartments()
    {
        // 1. Sync by employee_id_number using a subquery (PostgreSQL compatible)
        $subquery = \DB::table('employees')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereColumn('employees.employee_id', 'attendance_records.employee_id_number')
            ->select('departments.name')
            ->limit(1);

        \DB::table('attendance_records')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('employees')
                    ->whereColumn('employees.employee_id', 'attendance_records.employee_id_number');
            })
            ->update([
                'department' => \DB::raw("({$subquery->toSql()})")
            ]);

        // 2. Sync by name for any records that might still be mislabeled (fallback)
        // This handles cases where IDs might be different or records were imported by name
        $employees = Employee::with('department')->get();
        foreach ($employees as $employee) {
            if ($employee->department) {
                self::where('employee_name', 'IREGEXP', '^' . preg_quote($employee->first_name . ' ' . $employee->last_name) . '$')
                    ->where('department', '!=', $employee->department->name)
                    ->update(['department' => $employee->department->name]);
            }
        }
    }
}
