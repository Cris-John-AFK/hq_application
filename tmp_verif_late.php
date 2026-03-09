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
$month = 2;

$records = AttendanceRecord::whereYear('date', $year)->whereMonth('date', $month)->get();

$latesCount = $records->filter(function ($r) use ($service) {
    return $service->isLate($r->time_in, $r->status, $r->employee_id_number);
})->count();

echo "RESULT_START\n";
echo "LateCount:$latesCount\n";
echo "TardinessMins:" . ($latesCount * 30) . "\n";
echo "RESULT_END\n";
