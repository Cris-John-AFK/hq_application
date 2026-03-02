<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveAnalyticsDepartmentSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Department Breakdown';
    }

    public function headings(): array
    {
        return [
            ['DEPARTMENT LEAVE VOLUME BREAKDOWN'],
            ['Rank', 'Department Name', 'Total Filings', '% of Total Filings', 'Approved Requests', 'Total Days Taken']
        ];
    }

    public function collection()
    {
        $year = $this->filters['year'] ?? now()->year;
        $month = $this->filters['month'] ?? null;
        $status = $this->filters['status'] ?? null;
        $type = $this->filters['leave_type'] ?? null;

        $totalFilings = DB::table('leave_requests')
            ->where('is_archived', '!=', true)
            ->whereYear('from_date', $year)
            ->when($month, fn($q) => $q->whereMonth('from_date', $month))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('leave_type', $type))
            ->count();

        $depts = DB::table('leave_requests')
            ->leftJoin('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->leftJoin('departments as ed', 'employees.department_id', '=', 'ed.id')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('departments as ud', 'users.department_id', '=', 'ud.id')
            ->where('leave_requests.is_archived', '!=', true)
            ->whereYear('leave_requests.from_date', $year)
            ->when($month, fn($q) => $q->whereMonth('leave_requests.from_date', $month))
            ->when($status, fn($q) => $q->where('leave_requests.status', $status))
            ->when($type, fn($q) => $q->where('leave_requests.leave_type', $type))
            ->select(
                DB::raw("COALESCE(ed.name, ud.name, users.department, 'General') as name"),
                DB::raw('count(*) as count'),
                DB::raw("SUM(CASE WHEN leave_requests.status = 'Approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw('SUM(leave_requests.days_taken) as days')
            )
            ->groupBy(DB::raw("COALESCE(ed.name, ud.name, users.department, 'General')"))
            ->orderByDesc('count')
            ->get();

        $rows = collect();
        foreach ($depts as $idx => $dept) {
            $rows->push([
                $idx + 1,
                $dept->name,
                $dept->count,
                $totalFilings > 0 ? round(($dept->count / $totalFilings) * 100, 2) . '%' : '0%',
                $dept->approved,
                round($dept->days, 2)
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '065F46']]], // Emerald-900 background
        ];
    }
}
