<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check what months/years have records
$dates = \App\Models\AttendanceRecord::selectRaw("EXTRACT(YEAR FROM date) as yr, EXTRACT(MONTH FROM date) as mo, count(*) as cnt")
    ->groupByRaw("EXTRACT(YEAR FROM date), EXTRACT(MONTH FROM date)")
    ->orderByRaw("yr, mo")
    ->get();

foreach ($dates as $d) {
    echo "Year: {$d->yr} Month: {$d->mo} Count: {$d->cnt}\n";
}
