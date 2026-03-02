<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LeaveAnalyticsExport implements WithMultipleSheets
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new LeaveAnalyticsSummarySheet($this->filters),
            new LeaveAnalyticsMonthlyTrendSheet($this->filters),
            new LeaveAnalyticsDepartmentSheet($this->filters),
            new LeaveAnalyticsTypeSheet($this->filters),
        ];
    }
}
