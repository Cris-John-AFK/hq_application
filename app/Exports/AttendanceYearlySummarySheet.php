<?php

namespace App\Exports;

use App\Services\AttendanceReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceYearlySummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function title(): string
    {
        return 'Yearly Summary';
    }

    public function headings(): array
    {
        return [
            ['HATQ INC. - EXECUTIVE ATTENDANCE YEARLY SUMMARY'],
            ['FISCAL YEAR: ' . $this->year],
            ['Generated on: ' . now()->format('Y-m-d H:i')],
            [''],
            [
                'Month',
                'Total Working Days',
                'Total Employees',
                'Total Present Days',
                'Total Absent Days',
                'Total Tardiness (mins)',
                'Total Undertime (mins)'
            ]
        ];
    }

    public function collection()
    {
        $service = new AttendanceReportService();
        $annualData = $service->getAnnualReport($this->year);

        $summaryData = collect($annualData)->map(function ($row) {
            return [
                'month' => $row['month'],
                'total_working_days' => $row['total_working_days'],
                'headcount' => $row['headcount'],
                'total_present_days' => $row['total_present_days'],
                'total_absent_days' => $row['total_absent_days'],
                'total_tardiness_mins' => $row['employees_late'] * 2, // as per frontend logic
                'total_undertime_mins' => $row['total_undertime_mins']
            ];
        });

        return $summaryData;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '134E48']]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111827']] // Dark Gray (Gray-900)
            ],
            'A1:G5' => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }
}
