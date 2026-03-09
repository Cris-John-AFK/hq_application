<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Show sample records with dept info
$records = \App\Models\AttendanceRecord::whereYear('date', 2026)->whereMonth('date', 2)->limit(5)->get();
foreach ($records as $r) {
    $emp = \App\Models\Employee::where('employee_id', $r->employee_id_number)->first();
    echo "ID: {$r->employee_id_number} | DeptID: " . ($emp ? $emp->department_id : 'null') . " | Hours: {$r->hours_worked}\n";
}

echo "\nDept counts:\n";
$depts = \App\Models\Department::all();
foreach ($depts as $d) {
    $cnt = \App\Models\AttendanceRecord::whereYear('date', 2026)
        ->whereMonth('date', 2)
        ->whereHas('employee', fn($q) => $q->where('department_id', $d->id))
        ->count();
    if ($cnt > 0)
        echo "{$d->name}: $cnt records\n";
}
