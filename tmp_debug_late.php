<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AttendanceReportService();

$records = \App\Models\AttendanceRecord::where('status', 'Late')->limit(5)->get();
foreach ($records as $r) {
    echo "ID: $r->employee_id_number, IN: $r->time_in \n";
    echo "Lateness: " . $service->calculateLatenessMinutes($r->time_in, $r->employee_id_number) . "\n";
    $e = \App\Models\Employee::where('employee_id', $r->employee_id_number)->first();
    echo "Emp sched: " . ($e ? $e->working_hours : 'None') . "\n";
}
