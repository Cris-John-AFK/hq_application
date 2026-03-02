<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveAnalyticsMonthlyTrendSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Monthly & Weekly Trends';
    }

    public function headings(): array
    {
        return [
            ['MONTHLY LEAVE TRENDS - ' . ($this->filters['year'] ?? now()->year)],
            ['Month', 'Approved', 'Pending', 'Rejected', 'Cancelled', 'Total Filings', 'Approval Rate (%)']
        ];
    }

    public function collection()
    {
        $year = $this->filters['year'] ?? now()->year;
        $status = $this->filters['status'] ?? null;
        $type = $this->filters['leave_type'] ?? null;

        $trend = DB::table('leave_requests')
            ->select(
                DB::raw('EXTRACT(MONTH FROM from_date)::int as month'),
                DB::raw("SUM(CASE WHEN status = 'Approved'  THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'Pending'   THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'Rejected'  THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw('count(*) as total')
            )
            ->where('is_archived', '!=', true)
            ->whereYear('from_date', $year)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('leave_type', $type))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM from_date)'))
            ->orderBy('month')
            ->get();

        $rows = collect();
        $monthNames = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];

        foreach ($trend as $t) {
            $rate = $t->total > 0 ? round(($t->approved / $t->total) * 100, 2) . '%' : '0%';
            $rows->push([
                $monthNames[$t->month] ?? 'Month ' . $t->month,
                $t->approved,
                $t->pending,
                $t->rejected,
                $t->cancelled,
                $t->total,
                $rate
            ]);
        }

        $rows->push(['']);
        $rows->push(['WEEKLY TRENDS (Current Month)', 'Approved', 'Pending', 'Rejected', 'Cancelled', 'Total', 'Approval Rate']);

        // Weekly Trends (of current month or filtered month)
        $monthToWeekly = $this->filters['month'] ?? now()->month;
        $weeklyTrend = DB::table('leave_requests')
            ->select(
                DB::raw("floor((EXTRACT(DAY FROM from_date) - 1) / 7 + 1)::int as week"),
                DB::raw("SUM(CASE WHEN status = 'Approved'  THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'Pending'   THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'Rejected'  THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw('count(*) as total')
            )
            ->where('is_archived', '!=', true)
            ->whereYear('from_date', $year)
            ->whereMonth('from_date', $monthToWeekly)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('leave_type', $type))
            ->groupBy(DB::raw("floor((EXTRACT(DAY FROM from_date) - 1) / 7 + 1)"))
            ->orderBy('week')
            ->get();

        foreach ($weeklyTrend as $w) {
            $rate = $w->total > 0 ? round(($w->approved / $w->total) * 100, 2) . '%' : '0%';
            $rows->push([
                "Week " . $w->week . " of " . ($monthNames[$monthToWeekly] ?? 'Month'),
                $w->approved,
                $w->pending,
                $w->rejected,
                $w->cancelled,
                $w->total,
                $rate
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '115E59']]], // Teal-800 background
            16 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '115E59']]],
        ];
    }
}
