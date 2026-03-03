<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceReportsExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function sheets(): array
    {
        return [
            new AttendanceAnnualSheet($this->year),
            new AttendanceDepartmentSheet($this->year, $this->month),
            new AttendanceYearlySummarySheet($this->year),
        ];
    }
}
