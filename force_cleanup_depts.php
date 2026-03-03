<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceRecord;

function mergeDepartments($canonicalName, $searchPatterns)
{
    echo "Processing Merge for: $canonicalName\n";

    // 1. Find or create the target department
    $target = Department::whereRaw('LOWER(name) = ?', [strtolower($canonicalName)])->first();
    if (!$target) {
        $target = Department::create(['name' => $canonicalName]);
        echo "Created target: $canonicalName (ID: {$target->id})\n";
    } else {
        $target->update(['name' => $canonicalName]); // Standardize case
        echo "Found target: {$target->name} (ID: {$target->id})\n";
    }

    // 2. Find all others that match any of the patterns (ignoring case)
    foreach ($searchPatterns as $pattern) {
        $others = Department::where('name', 'ILIKE', $pattern)
            ->where('id', '!=', $target->id)
            ->get();

        foreach ($others as $other) {
            echo "Merging ID: {$other->id} ({$other->name}) -> ID: {$target->id}\n";
            Employee::where('department_id', $other->id)->update(['department_id' => $target->id]);
            $other->delete();
        }
    }
}

// Perform Merges
mergeDepartments('Maintenance and Engineering', ['Maintenance%Engineering', 'Maint%Eng%']);
mergeDepartments('Production', ['Production%', 'Prod%Dept%', 'Prod%Support%']);
mergeDepartments('General Admin Support', ['General%', 'Admin%Support%']);

echo "\n--- FINAL CLEANUP: Deleting any remaining empty departments ---\n";
Department::cleanup();

echo "\n--- GLOBAL SYNC: Updating Attendance Records to match Masterlist ---\n";
AttendanceRecord::syncDepartments();

echo "\n--- VERIFICATION ---\n";
$depts = Department::all();
foreach ($depts as $d) {
    $count = Employee::where('department_id', $d->id)->count();
    $archived = Employee::where('department_id', $d->id)->where('is_archived', true)->count();
    echo "DEPT: [{$d->name}] | ID: {$d->id} | Active Emps: " . ($count - $archived) . " | Archived: {$archived}\n";
}
