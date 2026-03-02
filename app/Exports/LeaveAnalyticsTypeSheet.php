<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveAnalyticsTypeSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'By Leave Type';
    }

    public function headings(): array
    {
        return [
            ['LEAVE TYPE ANALYTICS BREAKDOWN'],
            ['Leave Type', 'Total Filings', '% of Total Filings', 'Approved Requests', 'Total Days Taken']
        ];
    }

    public function collection()
    {
        $year = $this->filters['year'] ?? now()->year;
        $month = $this->filters['month'] ?? null;
        $week = $this->filters['week'] ?? null;
        $day = $this->filters['day'] ?? null;
        $status = $this->filters['status'] ?? null;
        $type = $this->filters['leave_type'] ?? null;

        $totalFilings = DB::table('leave_requests')
            ->where('is_archived', '!=', true)
            ->whereYear('from_date', $year)
            ->when($month, fn($q) => $q->whereMonth('from_date', $month))
            ->when($week && $month, fn($q) => $q->whereRaw("floor((EXTRACT(DAY FROM from_date) - 1) / 7 + 1) = ?", [$week]))
            ->when($day && $month, fn($q) => $q->whereDay('from_date', $day))
            ->count();

        $types = DB::table('leave_requests')
            ->where('is_archived', '!=', true)
            ->whereYear('from_date', $year)
            ->when($month, fn($q) => $q->whereMonth('from_date', $month))
            ->when($week && $month, fn($q) => $q->whereRaw("floor((EXTRACT(DAY FROM from_date) - 1) / 7 + 1) = ?", [$week]))
            ->when($day && $month, fn($q) => $q->whereDay('from_date', $day))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('leave_type', $type))
            ->select(
                'leave_type',
                DB::raw('count(*) as count'),
                DB::raw("SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw('SUM(days_taken) as days')
            )
            ->groupBy('leave_type')
            ->orderByDesc('count')
            ->get();

        $rows = collect();
        foreach ($types as $t) {
            $rows->push([
                $t->leave_type,
                $t->count,
                $totalFilings > 0 ? round(($t->count / $totalFilings) * 100, 2) . '%' : '0%',
                $t->approved,
                round($t->days, 2)
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '312E81']]], // Indigo-900 background
        ];
    }
}
