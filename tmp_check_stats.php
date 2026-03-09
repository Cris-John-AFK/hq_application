<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Employee;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026; // or 2025
$month = 2; // February

foreach ([2025, 2026] as $y) {
    echo "--- Stats for February $y ---\n";
    $totalEmployees = Employee::count();
    $records = AttendanceRecord::whereYear('date', $y)->whereMonth('date', $month)->get();
    $actualWorkingDays = AttendanceRecord::whereYear('date', $y)->whereMonth('date', $month)->distinct('date')->count('date');
    $presentCount = $records->whereIn('status', ['Present', 'Late'])->count();
    $absentCount = $records->where('status', 'Absent')->count();
    $lateCount = $records->where('status', 'Late')->count();
    $halfDayCount = $records->whereIn('status', ['Half Day', 'Undertime'])->count();
    $recordedCount = $records->count();

    $expectedPersonDays = $totalEmployees * $actualWorkingDays;
    $inferredAbsences = max(0, $expectedPersonDays - $recordedCount);
    $totalAbsent = $absentCount + $inferredAbsences;

    echo "Headcount: $totalEmployees\n";
    echo "Working Days: $actualWorkingDays\n";
    echo "Recorded Rows: $recordedCount\n";
    echo "Present/Late Rows: $presentCount\n";
    echo "Explicit Absent Rows: $absentCount\n";
    echo "Late Rows: $lateCount\n";
    echo "Half Day Rows: $halfDayCount\n";
    echo "Expected Person Days ($totalEmployees * $actualWorkingDays): $expectedPersonDays\n";
    echo "Total Absent (Explicit + Inferred): $totalAbsent\n";
    echo "Calculated Tardiness (Late * 30): " . ($lateCount * 30) . "\n";
    echo "Calculated Undertime (HalfDay * 240): " . ($halfDayCount * 240) . "\n\n";
}
