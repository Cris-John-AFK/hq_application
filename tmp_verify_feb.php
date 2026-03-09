<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Services\AttendanceReportService;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new AttendanceReportService();
$year = 2026;
$month = 2; // February

echo "VERIFYING February Stats\n";
$totalEmployees = Employee::count();
$records = AttendanceRecord::whereYear('date', $year)->whereMonth('date', $month)->get();

$actualWorkingDays = AttendanceRecord::whereYear('date', $year)
    ->whereMonth('date', $month)
    ->pluck('date')
    ->unique()
    ->count();

$presentCount = $records->filter(function ($r) {
    return in_array($r->status, ['Present', 'Late']);
})->count();

$explicitAbsences = $records->where('status', 'Absent')->count();
$recordedPersonDays = $records->count();
$expectedPersonDays = $totalEmployees * $actualWorkingDays;
$inferredAbsences = max(0, $expectedPersonDays - $recordedPersonDays);
$totalAbsent = $explicitAbsences + $inferredAbsences;

$latesCount = $records->filter(function ($r) use ($service) {
    return $service->isLate($r->time_in, $r->status, $r->employee_id_number);
})->count();

$undertimesCount = $records->whereIn('status', ['Half Day', 'Undertime'])->count();

echo "Headcount: $totalEmployees\n";
echo "Working Days: $actualWorkingDays\n";
echo "Present Days: $presentCount\n";
echo "Total Absent Days: $totalAbsent\n";
echo "Late Count: $latesCount\n";
echo "Undertimes Count: $undertimesCount\n";
echo "Tardiness Mins ($latesCount * 30): " . ($latesCount * 30) . "\n";
echo "Undertime Mins ($undertimesCount * 240): " . ($undertimesCount * 240) . "\n";
