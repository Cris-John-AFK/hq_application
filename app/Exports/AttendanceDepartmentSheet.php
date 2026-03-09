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
                'Actual Working Days',
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

        $mappedData = collect($data)->map(function ($row) {
            return [
                'department' => $row['department'],
                'total_employees' => $row['total_employees'],
                'total_working_days' => $row['total_working_days'],
                'total_scheduled_hours' => $row['total_scheduled_hours'],
                'total_actual_hours' => $row['total_actual_hours'],
                'regular_actual_hours' => $row['regular_actual_hours'],
                'overtime_actual_hours' => $row['overtime_actual_hours'],
                'excess_hours_worked' => $row['excess_hours_worked'],
                'employees_with_excess_ot' => $row['employees_with_excess_ot'],
                'avg_daily_working_hours' => $row['avg_daily_working_hours']
            ];
        });

        return $mappedData;
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
