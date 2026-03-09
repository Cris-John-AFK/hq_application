<?php
// Set internal encoding and display errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026; // Current year per local time
$service = new \App\Services\AttendanceReportService();
$data = $service->getAnnualReport($year);
echo json_encode($data);
