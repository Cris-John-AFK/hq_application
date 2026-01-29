<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Regular: " . \App\Models\Employee::where('employment_status', 'Regular')->count() . "\n";
echo "Probationary: " . \App\Models\Employee::where('employment_status', 'Probationary')->count() . "\n";
echo "Contractual: " . \App\Models\Employee::where('employment_status', 'Contractual')->count() . "\n";
