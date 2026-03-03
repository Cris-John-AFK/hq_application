<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceRecord;

return new class extends Migration {
    protected function merge($canonical, $patterns)
    {
        $target = Department::where('name', 'ILIKE', $canonical)->first();
        if (!$target) {
            $target = Department::create(['name' => $canonical]);
        } else {
            $target->update(['name' => $canonical]);
        }

        foreach ($patterns as $p) {
            $others = Department::where('name', 'ILIKE', $p)
                ->where('id', '!=', $target->id)
                ->get();

            foreach ($others as $other) {
                Employee::where('department_id', $other->id)->update(['department_id' => $target->id]);
                $other->delete();
            }
        }
        return $target;
    }

    public function up()
    {
        // 1. Aggressive Merges
        $this->merge('Maintenance and Engineering', ['Mainten%Engin%', 'Maint%Eng%']);
        $this->merge('Production', ['Production%', 'Prod%Dept%', 'Prod%Support%']);
        $this->merge('General Admin Support', ['General%', 'Admin%Support%']);
        $this->merge('Human Resource & Compliance', ['Human Resource%', 'HR%Admin%', 'HR & Admin']);

        // 2. Global Sync
        AttendanceRecord::syncDepartments();

        // 3. Final Cleanup - Only keep departments with ACTIVE employees
        // If a department only has archived employees, we'll keep it for now but
        // we'll advise the user to refresh.
        // Actually, let's just delete any department with 0 ACTIVE employees 
        // IF we can move their archived employees to a 'General' or similar dept?
        // No, let's just stick to the user's "no employees" rule.
        Department::cleanup();
    }

    public function down()
    {
    }
};
