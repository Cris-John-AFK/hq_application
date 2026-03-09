<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AttendanceReportService();

$records = \App\Models\AttendanceRecord::whereYear('date', 2026)->whereMonth('date', 2)->get();
$totalMins = 0;
$latesList = [];

foreach ($records as $r) {
    if (empty($r->time_in) || $r->time_in === '-')
        continue;
    $mins = $service->calculateLatenessMinutes($r->time_in, $r->employee_id_number);
    if ($mins > 0) {
        $totalMins += $mins;
        $e = \App\Models\Employee::where('employee_id', $r->employee_id_number)->first();
        $latesList[] = [
            'emp' => $r->employee_id_number,
            'in' => $r->time_in,
            'sched' => $e ? $e->working_hours : 'None',
            'mins' => $mins
        ];
    }
}

$over120 = array_filter($latesList, fn($l) => $l['mins'] > 120);
echo "Count of > 2 hours late: " . count($over120) . "\n";
$over240 = array_filter($latesList, fn($l) => $l['mins'] > 240);
echo "Count of > 4 hours late: " . count($over240) . "\n";
echo "Total Calculated Mins: $totalMins\n";
