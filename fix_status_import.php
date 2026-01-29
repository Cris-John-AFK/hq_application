<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Re-importing to fix employment statuses...\n";

try {
    // Truncate first to ensure clean state
    \DB::table('employee_details')->truncate();
    \DB::table('employees')->truncate();
    echo "Tables cleared.\n";

    \Maatwebsite\Excel\Facades\Excel::import(
        new \App\Imports\EmployeesImport, 
        'public/employee_masterlis.xls'
    );
    
    echo "✅ Success! Checking for Probationaries...\n";
    $probationCount = \App\Models\Employee::where('employment_status', 'Probationary')->count();
    $regularCount = \App\Models\Employee::where('employment_status', 'Regular')->count();
    
    echo "Summary:\n";
    echo "- Regular: $regularCount\n";
    echo "- Probationary: $probationCount\n";
    
    // Check ID 4125 (from user image)
    $emp4125 = \App\Models\Employee::where('employee_id', '4125')->first();
    if ($emp4125) {
        echo "Employee 4125 Status: {$emp4125->employment_status}\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
