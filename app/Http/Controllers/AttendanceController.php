<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::leftJoin('employees', 'attendance_records.employee_id_number', '=', 'employees.employee_id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select(
                'attendance_records.id',
                'attendance_records.employee_id_number',
                'attendance_records.employee_name',
                'attendance_records.date',
                'attendance_records.time_in',
                'attendance_records.time_out',
                'attendance_records.hours_worked',
                'attendance_records.status',
                'departments.name as employee_department',
                'employees.position as employee_position',
                'employees.avatar as avatar'
            );

        if ($request->has('start_date')) {
            $query->where('attendance_records.date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('attendance_records.date', '<=', $request->end_date);
        }
        if ($request->has('department')) {
            $query->where('attendance_records.department', $request->department);
        }
        if ($request->has('status')) {
            $query->where('attendance_records.status', $request->status);
        }
        if ($request->has('employee_id')) {
            $query->where('attendance_records.employee_id_number', $request->employee_id);
        }

        // Add limit support for dashboard performance
        $limit = $request->get('limit');
        if ($limit) {
            return response()->json($query->orderBy('attendance_records.date', 'desc')->take($limit)->get());
        }

        // If no filters and no limit, default to 100 to prevent massive payloads
        if (!$request->hasAny(['start_date', 'end_date', 'department', 'status', 'employee_id'])) {
            return response()->json($query->orderBy('attendance_records.date', 'desc')->take(100)->get());
        }

        return response()->json($query->orderBy('attendance_records.date', 'desc')->get());
    }

    public function bulkStore(Request $request)
    {
        $records = $request->input('records', []);

        foreach ($records as $record) {
            AttendanceRecord::updateOrCreate(
                [
                    'employee_id_number' => $record['employee_id_number'],
                    'date' => $record['date']
                ],
                [
                    'employee_name' => $record['employee_name'],
                    'time_in' => $record['time_in'],
                    'time_out' => $record['time_out'],
                    'hours_worked' => $record['hours_worked'],
                    'status' => $record['status'],
                    'department' => $record['department']
                ]
            );
        }

        return response()->json(['message' => 'Successfully imported attendance records.']);
    }

    public function roster(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        // Only get employees that actually have attendance records and are NOT archived
        $query = \App\Models\Employee::with('department')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('attendance_records')
                    ->whereColumn('attendance_records.employee_id_number', 'employees.employee_id');
            })
            ->where('is_archived', false)
            ->orderBy('first_name', 'asc');

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = strtoupper($request->search); // Match uppercase storage
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ILIKE', "%{$search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$search}%")
                    ->orWhere('employee_id', 'ILIKE', "%{$search}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function anomalies(Request $request)
    {
        $limit = $request->input('limit', 50);
        $query = AttendanceRecord::query()
            ->leftJoin('employees', 'attendance_records.employee_id_number', '=', 'employees.employee_id')
            ->where('employees.is_archived', false)
            ->where(function ($q) {
                $q->whereIn('attendance_records.status', ['Absent', 'Late', 'Half Day'])
                    ->orWhere('attendance_records.time_in', '-')
                    ->orWhere('attendance_records.time_out', '-');
            })
            ->select('attendance_records.*', 'employees.avatar as avatar')
            ->orderBy('attendance_records.date', 'desc')
            ->limit($limit);

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = strtoupper($request->search); // Match uppercase storage pattern
            $query->where(function ($q) use ($search) {
                $q->where('attendance_records.employee_name', 'ILIKE', "%{$search}%")
                    ->orWhere('attendance_records.employee_id_number', 'ILIKE', "%{$search}%")
                    ->orWhere('attendance_records.department', 'ILIKE', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    public function stats(Request $request)
    {
        $type = $request->input('type', 'daily');
        $data = [];

        if ($type === 'daily') {
            $startDate = now()->subDays(6)->startOfDay();
            $results = AttendanceRecord::where('date', '>=', $startDate)
                ->where('status', '!=', 'Absent')
                ->select(\DB::raw('date, count(*) as count'))
                ->groupBy('date')
                ->get()
                ->pluck('count', 'date');

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $data[] = [
                    'label' => date('M d', strtotime($date)),
                    'count' => $results->get($date, 0)
                ];
            }
        } elseif ($type === 'weekly') {
            $startDate = now()->subWeeks(3)->startOfWeek();
            $results = AttendanceRecord::where('date', '>=', $startDate)
                ->where('status', '!=', 'Absent')
                ->select(\DB::raw("to_char(date, 'IYYY-IW') as week, count(*) as count"))
                ->groupBy('week')
                ->get()
                ->pluck('count', 'week');

            for ($i = 3; $i >= 0; $i--) {
                $dt = now()->subWeeks($i);
                $weekKey = $dt->format('o-W'); // ISO year and week
                $start = $dt->copy()->startOfWeek();
                $end = $dt->copy()->endOfWeek();

                $data[] = [
                    'label' => $dt->format('M') . " wk" . (int) $dt->format('W') . " " . (int) $start->format('d') . "-" . (int) $end->format('d'),
                    'count' => $results->get($weekKey, 0)
                ];
            }
        } else {
            $startDate = now()->subMonths(5)->startOfMonth();
            $results = AttendanceRecord::where('date', '>=', $startDate)
                ->where('status', '!=', 'Absent')
                ->select(\DB::raw("to_char(date, 'YYYY-MM') as month, count(*) as count"))
                ->groupBy('month')
                ->get()
                ->pluck('count', 'month');

            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $monthKey = $dt->format('Y-m');
                $data[] = [
                    'label' => $dt->format('M'),
                    'count' => $results->get($monthKey, 0)
                ];
            }
        }

        return response()->json($data);
    }

    public function summary(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = AttendanceRecord::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->select('date', 'status', \DB::raw('count(*) as count'))
            ->groupBy('date', 'status')
            ->get();

        return response()->json($query);
    }
}
