<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\Department;

class AttendanceReportService
{
    public function getAnnualReport($year)
    {
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

        $data = [];
        $totalEmployees = Employee::count();

        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;

            $records = AttendanceRecord::whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->get();

            $workingDays = 22;
            $totalPossibleDays = $totalEmployees * $workingDays;
            $presentDays = $records->whereIn('status', ['Present', 'Late'])->count();
            $absentDays = $records->where('status', 'Absent')->count();

            $attendanceRate = $totalPossibleDays > 0 ? round(($presentDays / $totalPossibleDays) * 100, 2) : 0;
            $absenteeismRate = $totalPossibleDays > 0 ? round(($absentDays / $totalPossibleDays) * 100, 2) : 0;

            $data[] = [
                'month' => $monthName,
                'headcount' => $totalEmployees,
                'total_present_days' => $presentDays,
                'total_working_days' => $totalPossibleDays,
                'attendance_rate' => $attendanceRate . '%',
                'total_absent_days' => $absentDays,
                'absenteeism_rate' => $absenteeismRate . '%',
                'employees_late' => $records->where('status', 'Late')->count(),
                'tardiness_frequency' => $totalPossibleDays > 0 ? round(($records->where('status', 'Late')->count() / $totalPossibleDays) * 100, 2) . '%' : '0%',
                'employees_undertime' => $records->where('status', 'Half Day')->count(),
                'total_undertime_mins' => $records->where('status', 'Half Day')->count() * 240,
                'undertime_frequency' => $totalPossibleDays > 0 ? round(($records->where('status', 'Half Day')->count() / $totalPossibleDays) * 100, 2) . '%' : '0%'
            ];
        }

        return $data;
    }

    public function getMonthlyDepartmentReport($year, $month)
    {
        $departments = Department::all();
        $data = [];

        foreach ($departments as $dept) {
            $records = AttendanceRecord::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->where('department', $dept->name)
                ->get();

            $empCount = Employee::where('department_id', $dept->id)->count();
            $workingDays = 22;

            $actualHours = $records->sum('hours_worked');
            $scheduledHours = $empCount * $workingDays * 8;

            $data[] = [
                'department' => $dept->name,
                'total_employees' => $empCount,
                'total_working_days' => $empCount * $workingDays,
                'total_scheduled_hours' => $scheduledHours,
                'total_actual_hours' => $actualHours,
                'regular_actual_hours' => $actualHours,
                'overtime_actual_hours' => $records->where('status', 'Overtime')->sum('hours_worked'),
                'excess_hours_worked' => 0,
                'employees_with_excess_ot' => 0,
                'avg_daily_working_hours' => ($empCount * $workingDays) > 0 ? round($actualHours / ($empCount * $workingDays), 2) : 0
            ];
        }

        return $data;
    }
}
