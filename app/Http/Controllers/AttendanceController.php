<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::query();

        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->has('department')) {
            $query->where('department', $request->department);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('date', 'desc')->get());
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
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $count = AttendanceRecord::where('date', $date)
                    ->where('status', '!=', 'Absent')
                    ->count();

                $data[] = [
                    'label' => date('M d', strtotime($date)),
                    'count' => $count
                ];
            }
        } elseif ($type === 'weekly') {
            for ($i = 3; $i >= 0; $i--) {
                $dt = now()->subWeeks($i);
                $start = $dt->copy()->startOfWeek()->toDateString();
                $end = $dt->copy()->endOfWeek()->toDateString();

                $count = AttendanceRecord::whereBetween('date', [$start, $end])
                    ->where('status', '!=', 'Absent')
                    ->count();

                $data[] = [
                    'label' => "Week " . $dt->format('W'),
                    'count' => $count
                ];
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $month = $dt->month;
                $year = $dt->year;

                $count = AttendanceRecord::whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->where('status', '!=', 'Absent')
                    ->count();

                $data[] = [
                    'label' => $dt->format('M'),
                    'count' => $count
                ];
            }
        }

        return response()->json($data);
    }
}
