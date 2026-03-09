<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use App\Imports\Sheets\EmployeeMasterlistSheet;
use App\Imports\Sheets\EmployeeLeavesSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeesImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            // The first sheet is the masterlist (Index 0 in PhpSpreadsheet)
            0 => new EmployeeMasterlistSheet(),

            // The third sheet is the leave credits and working hours (Index 2 in PhpSpreadsheet)
            2 => new EmployeeLeavesSheet(),
        ];
    }
}
