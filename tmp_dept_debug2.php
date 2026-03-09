<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AttendanceReportService();
$res = $service->getMonthlyDepartmentReport(2026, 2);
foreach ($res as $r) {
    if ($r['total_working_days'] > 0 || $r['total_actual_hours'] > 0) {
        echo "{$r['department']}: Emp={$r['total_employees']} WkDays={$r['total_working_days']} SchedHrs={$r['total_scheduled_hours']} ActHrs={$r['total_actual_hours']}\n";
    }
}
