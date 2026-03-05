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
        $totalCompanyEmployees = Employee::count();

        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;

            $records = AttendanceRecord::whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->get();

            // Distinct dates with attendance in this month (actual working days)
            $actualWorkingDays = $records->pluck('date')->unique()->count();

            // Total possible days (everyone * actual days worked)
            // Or just calculate against the amount of data we *actually* have
            $totalPossibleDays = $totalCompanyEmployees * $actualWorkingDays;

            $presentDays = $records->filter(function ($r) {
                return in_array($r->status, ['Present', 'Late']);
            })->count();

            // Re-calculate absent days properly.
            $explicitAbsences = $records->where('status', 'Absent')->count();
            $inferredAbsences = $totalPossibleDays - $records->whereNotIn('status', ['Absent'])->count();
            $absentDays = max($explicitAbsences, $inferredAbsences);

            $attendanceRate = $totalPossibleDays > 0 ? round(($presentDays / $totalPossibleDays) * 100, 2) : 0;
            $absenteeismRate = $totalPossibleDays > 0 ? round(($absentDays / $totalPossibleDays) * 100, 2) : 0;

            $latesCount = $records->filter(function ($r) {
                return $this->isLate($r->time_in, $r->status);
            })->count();

            $undertimesCount = $records->whereIn('status', ['Half Day', 'Undertime'])->count();

            $data[] = [
                'month' => $monthName,
                'headcount' => $totalCompanyEmployees,
                'total_present_days' => $presentDays,
                'total_working_days' => $totalPossibleDays, // Based on actual logged active days
                'attendance_rate' => $attendanceRate . '%',
                'total_absent_days' => $absentDays,
                'absenteeism_rate' => $absenteeismRate . '%',
                'employees_late' => $latesCount,
                'tardiness_frequency' => $totalPossibleDays > 0 ? round(($latesCount / $totalPossibleDays) * 100, 2) . '%' : '0%',
                'employees_undertime' => $undertimesCount,
                'total_undertime_mins' => $undertimesCount * 240,
                'undertime_frequency' => $totalPossibleDays > 0 ? round(($undertimesCount / $totalPossibleDays) * 100, 2) . '%' : '0%'
            ];
        }

        return $data;
    }

    public function getMonthlyDepartmentReport($year, $month)
    {
        $departments = Department::all();
        $data = [];

        foreach ($departments as $dept) {
            // Fetch records for employees belonging to this department
            $records = AttendanceRecord::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereHas('employee', function ($query) use ($dept) {
                    $query->where('department_id', $dept->id);
                })
                ->get();

            $empCount = Employee::where('department_id', $dept->id)->count();

            // Distinct dates with attendance in this month for this department
            $actualWorkingDays = AttendanceRecord::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->pluck('date')
                ->unique()
                ->count();

            $totalPossibleDays = $empCount * $actualWorkingDays;

            $actualHours = $records->sum('hours_worked');
            $scheduledHours = $totalPossibleDays * 8;

            // Calculate regular vs overtime hours
            $recordsWithOvertime = $records->map(function ($record) {
                $hours = (float) $record->hours_worked;
                return [
                    'employee_id' => $record->employee_id_number,
                    'is_overtime' => $hours > 8,
                    'excess_hours' => max(0, $hours - 8)
                ];
            });

            $overtimeHours = $recordsWithOvertime->sum('excess_hours');
            $regularActualHours = max(0, $actualHours - $overtimeHours);
            $excessOvertimeHours = $recordsWithOvertime->where('excess_hours', '>', 2)->sum('excess_hours');
            $employeesWithExcessOt = $recordsWithOvertime->where('excess_hours', '>', 2)->pluck('employee_id')->unique()->count();

            $data[] = [
                'department' => $dept->name,
                'total_employees' => $empCount,
                'total_working_days' => $totalPossibleDays,
                'total_scheduled_hours' => $scheduledHours,
                'total_actual_hours' => round($actualHours, 2),
                'regular_actual_hours' => round($regularActualHours, 2),
                'overtime_actual_hours' => round($overtimeHours, 2),
                'excess_hours_worked' => round($excessOvertimeHours, 2),
                'employees_with_excess_ot' => $employeesWithExcessOt,
                'avg_daily_working_hours' => $totalPossibleDays > 0 ? round($actualHours / $totalPossibleDays, 2) : 0
            ];
        }

        return $data;
    }

    private function isLate($timeIn, $status)
    {
        if ($status === 'Late')
            return true;
        if (!$timeIn || $timeIn === '-')
            return false;

        try {
            // Assuming standard start time is 08:30 AM
            $time = \Carbon\Carbon::createFromFormat('h:i A', $timeIn);
            $cutoff = \Carbon\Carbon::createFromFormat('H:i', '08:30');
            return $time->gt($cutoff);
        } catch (\Exception $e) {
            return false;
        }
    }
}
