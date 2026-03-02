<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveAnalyticsSummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'KPI Summary';
    }

    public function headings(): array
    {
        return [
            ['LEAVE ANALYTICS KPI SUMMARY'],
            ['Generated on: ' . now()->format('Y-m-d H:i')],
            [''],
            ['KPI Metric', 'Value', 'Percentage (%)']
        ];
    }

    public function collection()
    {
        $year = $this->filters['year'] ?? now()->year;
        $month = $this->filters['month'] ?? null;
        $status = $this->filters['status'] ?? null;
        $type = $this->filters['leave_type'] ?? null;

        $base = LeaveRequest::where('is_archived', '!=', true)
            ->whereYear('from_date', $year);

        if ($month)
            $base->whereMonth('from_date', $month);
        if ($status)
            $base->where('status', $status);
        if ($type)
            $base->where('leave_type', $type);

        $total = $base->count();
        $approved = (clone $base)->where('status', 'Approved')->count();
        $pending = (clone $base)->where('status', 'Pending')->count();
        $rejected = (clone $base)->where('status', 'Rejected')->count();
        $cancelled = (clone $base)->where('status', 'Cancelled')->count();
        $daysTaken = (clone $base)->sum('days_taken');
        $avgDays = $total > 0 ? round($daysTaken / $total, 2) : 0;

        $approvedPaid = (clone $base)->where('status', 'Approved')->where('is_paid', true)->count();
        $approvedUnpaid = (clone $base)->where('status', 'Approved')->where('is_paid', false)->count();

        $rows = collect([
            ['Total Leave Filings', $total, '100%'],
            ['Approved', $approved, ($total > 0 ? round($approved / $total * 100, 2) . '%' : '0%')],
            ['Pending', $pending, ($total > 0 ? round($pending / $total * 100, 2) . '%' : '0%')],
            ['Rejected', $rejected, ($total > 0 ? round($rejected / $total * 100, 2) . '%' : '0%')],
            ['Cancelled', $cancelled, ($total > 0 ? round($cancelled / $total * 100, 2) . '%' : '0%')],
            ['Total Days Consumed', $daysTaken, '-'],
            ['Average Days / Filing', $avgDays, '-'],
            [''],
            ['PAY CLASSIFICATION (Approved Only)', '', ''],
            ['With Pay (Approved)', $approvedPaid, ($approved > 0 ? round($approvedPaid / $approved * 100, 2) . '%' : '0%')],
            ['Without Pay (Approved)', $approvedUnpaid, ($approved > 0 ? round($approvedUnpaid / $approved * 100, 2) . '%' : '0%')],
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '134E48']]], // Teal-900 background
            9 => ['font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => '475569']]], // Pay Breakdown header
        ];
    }
}
