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
        return [];
    }

    public function collection()
    {
        $service = new AttendanceReportService();
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

        // Ensure we always map 12 rows
        $dataRows = [];
        foreach ($months as $month) {
            $monthData = collect($annualData)->firstWhere('month', $month);

            $dataRows[] = [
                'Month' => $month,
                'Headcount' => $monthData ? $monthData['headcount'] : '0',
                'Total Present Days' => $monthData ? $monthData['total_present_days'] : '0',
                'Total Working Days' => $monthData ? $monthData['total_working_days'] : '0',
                'Attendance Rate (%)' => $monthData && $monthData['possible_person_days'] > 0 ? $monthData['attendance_rate'] : '-',
                'Total Absent Days' => $monthData ? $monthData['total_absent_days'] : '0',
                'Absenteeism Rate (%)' => $monthData && $monthData['possible_person_days'] > 0 ? $monthData['absenteeism_rate'] : '-',
                'No. of employees late' => $monthData ? $monthData['employees_late'] : '0',
                'Tardiness Frequency (%)' => $monthData && $monthData['possible_person_days'] > 0 ? $monthData['tardiness_frequency'] : '-',
                'No. of employees undertime/half day' => $monthData ? $monthData['employees_undertime'] : '0',
                'Undertime / Half day Frequency (%)' => $monthData && $monthData['possible_person_days'] > 0 ? $monthData['undertime_frequency'] : '-'
            ];
        }

        $rows = [
            ['HATQ INC.'],
            ['FISCAL YEAR: ' . $this->year],
            ['Generated on: ' . now()->format('Y-m-d H:i')],
            [''],

            // Header Row 1
            [
                'YEAR',
                $this->year,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            // Header Row 2
            [
                'Month',
                'Headcount',
                'Total Present Days',
                'Total Working Days',
                'Attendance Rate (%)',
                'Total Absent Days',
                'Absenteeism Rate (%)',
                'No. of employees late',
                'Tardiness Frequency (%)',
                'No. of employees undertime/half day',
                'Undertime / Half day Frequency (%)'
            ],
        ];

        return collect(array_merge($rows, $dataRows));
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
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111827']], // Dark Gray (Gray-900)
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'wrapText' => true]
            ],
            'A5:A6' => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
            ],
            // The empty spacer columns H and J
            'H6:H18' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3F4F6']]
            ],
            'J6:J18' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3F4F6']]
            ],

            'A6:K18' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]
        ];
    }
}
