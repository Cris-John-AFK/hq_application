<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceRecord;

return new class extends Migration {
    public function up()
    {
        // 1. Define the merge map [Source ID => Target ID]
        // Target 33: Maintenance And Engineering
        // Target 37: Production Dept.
        // Target 35: General Admin Support
        $mergeMap = [
            11 => 33, // Maintenance & Engineering -> Maintenance And Engineering
            1 => 37,  // Production -> Production Dept.
            25 => 37, // Production Support Group -> Production Dept.
            14 => 35, // General -> General Admin Support
        ];

        foreach ($mergeMap as $sourceId => $targetId) {
            $source = Department::find($sourceId);
            $target = Department::find($targetId);

            if ($source && $target) {
                // Reassign employees
                Employee::where('department_id', $sourceId)->update(['department_id' => $targetId]);

                // Delete the source department
                $source->delete();
            }
        }

        // 2. Fix spelling of "Maintenance And Engineering" if preferred?
        // User said: "Maintenance and engineering not mantainance & enginerrign"
        // Let's ensure the target name is clean.
        $maintenance = Department::find(33);
        if ($maintenance) {
            $maintenance->update(['name' => 'Maintenance and Engineering']);
        }

        // 3. Sync ALL Attendance Records to match their Employee's current department name
        $employees = Employee::with('department')->get();
        foreach ($employees as $employee) {
            if ($employee->department) {
                AttendanceRecord::where('employee_id_number', $employee->employee_id)
                    ->update(['department' => $employee->department->name]);

                // Also check by name as fallback for records imported without ID matching correctly
                AttendanceRecord::where('employee_name', $employee->first_name . ' ' . $employee->last_name)
                    ->update(['department' => $employee->department->name]);
            }
        }
    }

    public function down()
    {
        // One-way migration
    }
};
