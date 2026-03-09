<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test actual department filter
$records = \App\Models\AttendanceRecord::whereYear('date', 2026)
    ->whereMonth('date', 2)
    ->whereHas('employee', fn($q) => $q->where('department_id', 36))
    ->get();

echo "Dept 36 (Feb 2026) record count via whereYear/whereMonth: " . $records->count() . "\n";

// Try alternate way
$records2 = \App\Models\AttendanceRecord::whereBetween('date', ['2026-02-01', '2026-02-28'])
    ->whereHas('employee', fn($q) => $q->where('department_id', 36))
    ->get();

echo "Dept 36 (Feb 2026) record count via whereBetween: " . $records2->count() . "\n";
echo "Hours sum: " . $records2->sum('hours_worked') . "\n";
echo "Unique dates: " . $records2->pluck('date')->unique()->count() . "\n";
