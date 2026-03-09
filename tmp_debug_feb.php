<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AttendanceReportService();
$res = $service->getAnnualReport(2026);
echo "FEB: \n";
print_r(collect($res)->firstWhere('month', 'February'));
