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
        return [
            ['HATQ INC. - ANNUAL COMPANY ATTENDANCE REPORT'],
            ['FOR THE YEAR: ' . $this->year],
            ['Generated on: ' . now()->format('Y-m-d H:i')],
            [''],
            [
                'Month',
                'Headcount',
                'Total Present Days',
                'Total Working Days',
                'Attendance Rate (%)',
                'Total Absent Days',
                'Absenteeism Rate (%)',
                'Late Count',
                'Tardiness Freq (%)',
                'Undertime / Half Day',
                'Total Undertime (mins)',
                'Undertime Freq (%)'
            ]
        ];
    }

    public function collection()
    {
        $service = new AttendanceReportService();
        $data = $service->getAnnualReport($this->year);

        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '134E48']]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111827']] // Dark Gray (Gray-900)
            ],
            'A1:L' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ],
        ];
    }
}
