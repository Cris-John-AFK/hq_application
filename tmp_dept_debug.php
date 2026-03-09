<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AttendanceReportService();

echo "=== Feb 2026 ===\n";
$res = $service->getMonthlyDepartmentReport(2026, 2);
foreach ($res as $r) {
    echo "{$r['department']}: Emp={$r['total_employees']} WkDays={$r['total_working_days']} SchedHrs={$r['total_scheduled_hours']} ActHrs={$r['total_actual_hours']}\n";
}

echo "\n=== Feb 2025 ===\n";
$res = $service->getMonthlyDepartmentReport(2025, 2);
foreach ($res as $r) {
    echo "{$r['department']}: Emp={$r['total_employees']} WkDays={$r['total_working_days']} SchedHrs={$r['total_scheduled_hours']} ActHrs={$r['total_actual_hours']}\n";
}

echo "\n=== Jan 2026 ===\n";
$res = $service->getMonthlyDepartmentReport(2026, 1);
foreach ($res as $r) {
    echo "{$r['department']}: WkDays={$r['total_working_days']} SchedHrs={$r['total_scheduled_hours']} ActHrs={$r['total_actual_hours']}\n";
}
