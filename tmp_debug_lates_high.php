<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AttendanceReportService();

$records = \App\Models\AttendanceRecord::whereYear('date', 2026)->whereMonth('date', 2)->get();

foreach ($records as $r) {
    if (empty($r->time_in) || $r->time_in === '-')
        continue;
    $mins = $service->calculateLatenessMinutes($r->time_in, $r->employee_id_number);
    if ($mins > 240) {
        $e = \App\Models\Employee::where('employee_id', $r->employee_id_number)->first();
        echo "IN: $r->time_in | SCHED: " . ($e ? $e->working_hours : 'None') . " | MINS: $mins\n";
    }
}

