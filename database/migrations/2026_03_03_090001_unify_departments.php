<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceRecord;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Unify Maintenance and Engineering
        $this->merge('Maintenance and Engineering', [
            'Maintenance & Engineering',
            'Maintenance And Engineering',
            'Maintenance%Engineering',
            'Maint%Eng%'
        ]);

        // 2. Unify Production
        $this->merge('Production', [
            'Production Dept.',
            'Production Support Group',
            'Production%',
            'Prod%Dept%',
            'Prod%Support%'
        ]);

        // 3. Unify General Admin Support
        $this->merge('General Admin Support', [
            'General',
            'General%',
            'Admin%Support%'
        ]);

        // 4. Unify HR
        $this->merge('Human Resource & Compliance', [
            'Human Resource%',
            'HR%Admin%',
            'HR & Admin'
        ]);

        // 5. Global sync of attendance records
        AttendanceRecord::syncDepartments();

        // 6. Final cleanup of empty departments
        Department::cleanup();
    }

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

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Non-reversible data sync
    }
};
