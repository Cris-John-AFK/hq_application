<?php

namespace App\Exports;

use App\Services\AttendanceReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceDepartmentSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function title(): string
    {
        $monthName = date('F', mktime(0, 0, 0, $this->month, 10));
        return "Monthly Dept. ({$monthName})";
    }

    public function headings(): array
    {
        $monthName = date('F', mktime(0, 0, 0, $this->month, 10));
        return [
            ['HATQ INC. - MONTHLY DEPARTMENTAL PERFORMANCE REPORT'],
            ["MONTH: {$monthName} {$this->year}"],
            ['Generated on: ' . now()->format('Y-m-d H:i')],
            [''],
            [
                'Department',
                'Total Employees',
                'Total Working Days',
                'Total Scheduled Hrs',
                'Total Actual Hrs',
                'Regular Actual Hrs',
                'Overtime Hours',
                'Excess Hours (>2)',
                '# Emp w/ Excess OT',
                'Avg Daily Hrs / Emp'
            ]
        ];
    }

    public function collection()
    {
        $service = new AttendanceReportService();
        $data = $service->getMonthlyDepartmentReport($this->year, $this->month);

        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '134E48']]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111827']] // Dark Gray (Gray-900)
            ]
        ];
    }
}
