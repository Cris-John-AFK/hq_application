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

            // More realistic possible days based on actual records plus explicit absent records
            $explicitAbsences = $records->where('status', 'Absent')->count();

            $presentDays = $records->filter(function ($r) {
                return in_array($r->status, ['Present', 'Late']);
            })->count();

            $undertimesCount = $records->whereIn('status', ['Half Day', 'Undertime'])->count();

            // Re-calculate absent days properly.
            // If the user's data does not contain every employee every day, 
            // relying on $totalCompanyEmployees * $actualWorkingDays gives a huge false number for "Absent"
            // We use the recorded days + known absences to base our calculations on explicit data.
            $recordedPersonDays = $records->count();
            $totalPossibleDays = $recordedPersonDays; // Using actual records as base

            // If you want to assume all employees should have worked $actualWorkingDays:
            $expectedPersonDays = $totalCompanyEmployees * $actualWorkingDays;

            // To fix the user issue ("6200+ working days"), we will use $expectedPersonDays as possible days, 
            // but ONLY if it seems realistic. Actually, let's use the explicit records for accurate metrics if imports are partial.
            // But usually "Total Working Days" means the distinct days.
            // "Possible Person Days" = Total Employees * Total Working Days.
            $totalPossibleDays = $expectedPersonDays;

            $inferredAbsences = max(0, $totalPossibleDays - $recordedPersonDays);
            $absentDays = $explicitAbsences + $inferredAbsences;

            $attendanceRate = $totalPossibleDays > 0 ? round((($presentDays + $undertimesCount) / $totalPossibleDays) * 100, 2) : 0;
            $absenteeismRate = $totalPossibleDays > 0 ? round(($absentDays / $totalPossibleDays) * 100, 2) : 0;

            $latesCount = $records->filter(function ($r) {
                return $this->isLate($r->time_in, $r->status, $r->employee_id_number);
            })->count();

            $undertimesCount = $records->whereIn('status', ['Half Day', 'Undertime'])->count();

            $data[] = [
                'month' => $monthName,
                'headcount' => $totalCompanyEmployees,
                'total_present_days' => $presentDays,
                'total_working_days' => $actualWorkingDays, // Actual chronological days (e.g., 5 or 20)
                'possible_person_days' => $totalPossibleDays, // Person-days for rate calculations
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

            $recordedPersonDays = $records->count();
            // Expected
            $expectedPersonDays = $empCount * $actualWorkingDays;

            // To fix inflated rates:
            $totalPossibleDays = $expectedPersonDays;

            $actualHours = $records->sum('hours_worked');
            // Assuming 8 hours scheduled for possible days
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
                'total_working_days' => $actualWorkingDays, // Actual chronological days
                'possible_person_days' => $totalPossibleDays,
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

    public function isLate($timeIn, $status, $employeeIdNumber = null)
    {
        if ($status === 'Late')
            return true;
        if (!$timeIn || $timeIn === '-')
            return false;

        try {
            $time = \Carbon\Carbon::createFromFormat('h:i A', $timeIn);

            // Try to find the exact schedule for this employee
            if ($employeeIdNumber) {
                // Since employee_id in AttendanceRecord matches employee_id in Employee
                $employee = Employee::where('employee_id', $employeeIdNumber)->first();
                if ($employee && $employee->working_hours) {
                    // Extract the "7:00 AM" side of "7:00 AM - 3:00 PM"
                    $parts = explode('-', $employee->working_hours);
                    if (count($parts) > 0) {
                        $startTimeStr = trim($parts[0]);
                        try {
                            // Trim and ensure format like "7:00 AM" maps to "g:i A", string could be "07:00 AM" ("h:i A")
                            // We use parse to be more flexible against variations like 7:00AM vs 07:00 AM
                            $cutoff = \Carbon\Carbon::parse($startTimeStr);

                            // Align date part just for comparison ease (same day)
                            $cutoff->setDate($time->year, $time->month, $time->day);

                            // Lateness grace period: let's stick to exact cutoff or 1 min grace to be safe
                            return $time->gt($cutoff);
                        } catch (\Exception $e) {
                            // Fallthrough on format error
                        }
                    }
                }
            }

            // Fallback: Default schedule cutoff (08:30 AM)
            $cutoff = \Carbon\Carbon::createFromFormat('H:i', '08:30');
            $cutoff->setDate($time->year, $time->month, $time->day);
            return $time->gt($cutoff);
        } catch (\Exception $e) {
            return false;
        }
    }
}
