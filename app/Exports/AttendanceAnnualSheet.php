<?php

namespace App\Exports;

use App\Services\AttendanceReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceAnnualSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function title(): string
    {
        return 'Annual Company Report';
    }

    public function headings(): array
    {
        return [];
    }

    public function collection()
    {
        $service = new \App\Services\AttendanceReportService();
        $annualData = $service->getAnnualReport($this->year);

        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        // Create the structured array
        $rows = [
            // Row 1: Header Top
            ['HATQ INC. - ANNUAL COMPANY ATTENDANCE REPORT'],
            ['FOR THE YEAR: ' . $this->year],
            ['Generated on: ' . now()->format('Y-m-d H:i')],
            [''],

            // Row 5: Table Header 1
            array_merge(['YEAR', $this->year], array_fill(0, 12, '')),

            // Row 6: Table Header 2
            array_merge(['Metric', 'Details'], $months),

            // Data Rows
            $this->buildRow('Total Working Days', 'Calendar working days - official holidays', 'total_working_days', $annualData, $months),
            $this->buildRow('Total Employees', 'Headcount at start of period', 'headcount', $annualData, $months),
            $this->buildRow('Total Present Days', 'Σ Present days for all employees', 'total_present_days', $annualData, $months),
            $this->buildRow('Total Absent Days', 'Σ Absent days for all employees', 'total_absent_days', $annualData, $months),
            $this->buildTardinessRow('Total Tardiness (mins)', 'Σ minutes late for all employees', $annualData, $months),
            $this->buildRow('Total Undertimes/Half day (mins)', 'Σ minutes early departure', 'total_undertime_mins', $annualData, $months),
        ];

        return collect($rows);
    }

    private function buildRow($metric, $details, $key, $data, $months)
    {
        $row = [$metric, $details];
        foreach ($months as $month) {
            $monthData = collect($data)->firstWhere('month', $month);
            $row[] = $monthData ? $monthData[$key] : '';
        }
        return $row;
    }

    private function buildTardinessRow($metric, $details, $data, $months)
    {
        $row = [$metric, $details];
        foreach ($months as $month) {
            $monthData = collect($data)->firstWhere('month', $month);
            $row[] = $monthData ? $monthData['total_late_mins'] : '';
        }
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '134E48']]],
            2 => ['font' => ['bold' => true, 'size' => 12]],

            // Styling the Header Rows
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111827']], // Dark
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],
            6 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']], // Light Gray
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],

            'A7:A12' => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3F4F6']]
            ],

            'A5:N12' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]
        ];
    }
}
