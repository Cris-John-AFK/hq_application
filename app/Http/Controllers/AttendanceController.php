<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Use a join to get the employee's avatar directly for performance
        $query = AttendanceRecord::query()
            ->leftJoin('employees', 'attendance_records.employee_id_number', '=', 'employees.employee_id')
            ->select('attendance_records.*', 'employees.avatar as avatar');

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

        // Add limit support for dashboard performance
        $limit = $request->get('limit');
        if ($limit) {
            return response()->json($query->orderBy('attendance_records.date', 'desc')->take($limit)->get());
        }

        // If no filters and no limit, default to 100 to prevent massive payloads
        if (!$request->hasAny(['start_date', 'end_date', 'department', 'status'])) {
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
                $data[] = [
                    'label' => "Week " . $dt->format('W'),
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
}
