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

        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;
            $startDate = \Carbon\Carbon::create($year, $monthNum, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // Headcount: hired on or before end of this month, and active (or archived AFTER the start of the month)
            $headcount = Employee::where('date_hired', '<=', $endDate->format('Y-m-d'))
                ->where(function ($query) use ($startDate) {
                    $query->where('is_archived', false)
                        ->orWhere('archived_at', '>=', $startDate->format('Y-m-d H:i:s'));
                })
                ->count();

            $records = AttendanceRecord::whereBetween('date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])->get();

            $actualWorkingDays = $records->pluck('date')->unique()->count();

            $explicitAbsences = 0;
            $presentDays = 0;
            $latesCount = 0;
            $totalLateMins = 0;
            $undertimesCount = 0;
            $totalUndertimeMins = 0;

            foreach ($records as $record) {
                // If they lack a clock-in or clock-out, strictly count them as absent
                if ($record->status === 'Absent' || empty($record->time_in) || empty($record->time_out) || $record->time_in === '-' || $record->time_out === '-') {
                    $explicitAbsences++;
                    continue; // Skip tardiness and undertime points if absent
                }

                // They are present and have valid times
                if (in_array($record->status, ['Half Day', 'Undertime'])) {
                    $undertimesCount++;
                    $totalUndertimeMins += $this->calculateUndertimeMinutes($record->time_out, $record->hours_worked, $record->employee_id_number);
                } elseif (in_array($record->status, ['Present', 'Late']) || $record->hours_worked > 0) {
                    $presentDays++;
                }

                // Check Lateness Separately
                if ($this->isLate($record->time_in, $record->status, $record->employee_id_number)) {
                    $latesCount++;
                    $totalLateMins += $this->calculateLatenessMinutes($record->time_in, $record->employee_id_number);
                }
            }

            $recordedPersonDays = $records->count();
            $expectedPersonDays = $headcount * $actualWorkingDays;
            $inferredAbsences = max(0, $expectedPersonDays - $recordedPersonDays);
            $absentDays = $explicitAbsences + $inferredAbsences;

            $totalPossibleDays = $expectedPersonDays;

            $attendanceRate = $totalPossibleDays > 0 ? round((($presentDays + $undertimesCount) / $totalPossibleDays) * 100, 2) : 0;
            $absenteeismRate = $totalPossibleDays > 0 ? round(($absentDays / $totalPossibleDays) * 100, 2) : 0;

            $data[] = [
                'month' => $monthName,
                'headcount' => $headcount,
                'total_present_days' => $presentDays,
                'total_working_days' => $actualWorkingDays,
                'possible_person_days' => $totalPossibleDays,
                'attendance_rate' => $attendanceRate . '%',
                'total_absent_days' => $absentDays,
                'absenteeism_rate' => $absenteeismRate . '%',
                'employees_late' => $latesCount,
                'total_late_mins' => $totalLateMins,
                'tardiness_frequency' => $totalPossibleDays > 0 ? round(($latesCount / $totalPossibleDays) * 100, 2) . '%' : '0%',
                'employees_undertime' => $undertimesCount,
                'total_undertime_mins' => $totalUndertimeMins,
                'undertime_frequency' => $totalPossibleDays > 0 ? round(($undertimesCount / $totalPossibleDays) * 100, 2) . '%' : '0%'
            ];
        }

        return $data;
    }

    public function getMonthlyDepartmentReport($year, $month)
    {
        $departments = Department::all();
        $data = [];
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Company-wide distinct working days (dates that have ANY record)
        $companyWorkingDays = AttendanceRecord::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])
            ->pluck('date')
            ->unique()
            ->count();

        foreach ($departments as $dept) {
            // Only load records for employees in this department
            $records = AttendanceRecord::whereBetween('date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
                ->whereHas('employee', function ($query) use ($dept) {
                    $query->where('department_id', $dept->id);
                })
                ->with('employee')
                ->get();

            // Active employees in this department for this reporting month
            $employees = Employee::where('department_id', $dept->id)
                ->where('date_hired', '<=', $endDate->format('Y-m-d'))
                ->where(function ($query) use ($startDate) {
                    $query->where('is_archived', false)
                        ->orWhere('archived_at', '>=', $startDate->format('Y-m-d H:i:s'));
                })
                ->get();

            $empCount = $employees->count();

            // Actual working days = unique dates with records for THIS department
            $deptWorkingDays = $records->pluck('date')->unique()->count();
            // If dept has no records yet, fall back to company calendar
            $actualWorkingDays = $deptWorkingDays > 0 ? $deptWorkingDays : $companyWorkingDays;

            // Scheduled hours: sum each employee's individual shift length × working days
            $totalScheduledHours = 0;
            foreach ($employees as $emp) {
                $shiftHours = 8; // default
                if ($emp->working_hours) {
                    $parts = explode('-', $emp->working_hours);
                    if (count($parts) === 2) {
                        try {
                            $start = \Carbon\Carbon::parse(trim($parts[0]));
                            $end = \Carbon\Carbon::parse(trim($parts[1]));
                            // Handle overnight shifts (end < start)
                            if ($end->lt($start))
                                $end->addDay();
                            $shiftHours = round($start->diffInMinutes($end) / 60, 2);
                        } catch (\Exception $e) {
                        }
                    }
                }
                $totalScheduledHours += $shiftHours * $actualWorkingDays;
            }

            $actualHours = $records->sum('hours_worked');

            // Per-record overtime calculation
            $overtimeHours = 0;
            $excessOtHours = 0;
            $excessOtEmpIds = [];

            foreach ($records as $record) {
                $emp = $record->employee;
                $shiftHours = 8;
                if ($emp && $emp->working_hours) {
                    $parts = explode('-', $emp->working_hours);
                    if (count($parts) === 2) {
                        try {
                            $s = \Carbon\Carbon::parse(trim($parts[0]));
                            $e = \Carbon\Carbon::parse(trim($parts[1]));
                            if ($e->lt($s))
                                $e->addDay();
                            $shiftHours = round($s->diffInMinutes($e) / 60, 2);
                        } catch (\Exception $ex) {
                        }
                    }
                }
                $worked = (float) $record->hours_worked;
                $excess = max(0, $worked - $shiftHours);
                $overtimeHours += $excess;
                if ($excess > 2) {
                    $excessOtHours += $excess;
                    $excessOtEmpIds[] = $record->employee_id_number;
                }
            }

            $regularActualHours = max(0, $actualHours - $overtimeHours);
            $employeesWithExcessOt = count(array_unique($excessOtEmpIds));
            $totalPossibleDays = $empCount * $actualWorkingDays;

            $data[] = [
                'department' => $dept->name,
                'total_employees' => $empCount,
                'total_working_days' => $actualWorkingDays,
                'possible_person_days' => $totalPossibleDays,
                'total_scheduled_hours' => round($totalScheduledHours, 2),
                'total_actual_hours' => round($actualHours, 2),
                'regular_actual_hours' => round($regularActualHours, 2),
                'overtime_actual_hours' => round($overtimeHours, 2),
                'excess_hours_worked' => round($excessOtHours, 2),
                'employees_with_excess_ot' => $employeesWithExcessOt,
                'avg_daily_working_hours' => $totalPossibleDays > 0
                    ? round($actualHours / $totalPossibleDays, 2)
                    : 0
            ];
        }

        return $data;
    }

    public function calculateLatenessMinutes($timeIn, $employeeIdNumber)
    {
        if (!$timeIn || $timeIn === '-')
            return 0;

        try {
            $time = \Carbon\Carbon::parse($timeIn);
            $cutoff = null;

            if ($employeeIdNumber) {
                $employee = Employee::where('employee_id', $employeeIdNumber)->first();
                if ($employee && $employee->working_hours) {
                    $parts = explode('-', $employee->working_hours);
                    if (count($parts) > 0) {
                        $cutoff = \Carbon\Carbon::parse(trim($parts[0]));
                    }
                }
            }

            if (!$cutoff) {
                $cutoff = \Carbon\Carbon::parse('08:30 AM');
            }

            $cutoff->setDate($time->year, $time->month, $time->day);

            if ($time->gt($cutoff)) {
                $mins = (int) abs($time->diffInMinutes($cutoff, false));
                // Cap late physical minutes to 480 (8 hours) so night-shift/anomalies don't generate 700+ mins.
                return min(480, $mins);
            }
        } catch (\Exception $e) {
        }

        return 0;
    }

    public function calculateUndertimeMinutes($timeOut, $hoursWorked, $employeeIdNumber)
    {
        // If they worked 0 hours, it's 8 hours (480 mins) orhandled as absence? 
        // Image logic: "Undertimes/Half day (mins)" usually refers to the lost time.
        // If Half Day (worked < 5 hrs), we assume they missed 4 hours (240 mins).
        // If clocked out early, we check against their schedule end.

        if ($hoursWorked > 0 && $hoursWorked < 5.0) {
            return 240; // 4 hours loss for Half Day
        }

        if (!$timeOut || $timeOut === '-')
            return 0;

        try {
            $time = \Carbon\Carbon::parse($timeOut);
            $finish = null;

            if ($employeeIdNumber) {
                $employee = Employee::where('employee_id', $employeeIdNumber)->first();
                if ($employee && $employee->working_hours) {
                    $parts = explode('-', $employee->working_hours);
                    if (count($parts) > 1) {
                        $finish = \Carbon\Carbon::parse(trim($parts[1]));
                    }
                }
            }

            if (!$finish) {
                $finish = \Carbon\Carbon::parse('05:30 PM');
            }

            $finish->setDate($time->year, $time->month, $time->day);

            if ($time->lt($finish)) {
                $mins = (int) abs($finish->diffInMinutes($time, false));
                // Cap undertime to 480 (8 hours max)
                return min(480, $mins);
            }
        } catch (\Exception $e) {
        }

        return 0;
    }

    public function isLate($timeIn, $status, $employeeIdNumber = null)
    {
        return $this->calculateLatenessMinutes($timeIn, $employeeIdNumber) > 0;
    }

    public function calculateStatus($timeIn, $timeOut, $hoursWorked, $employeeIdNumber, $date)
    {
        $employee = Employee::where('employee_id', $employeeIdNumber)->first();
        $hoursWorked = (float) $hoursWorked;

        // 1. Check for Approved Leaves
        $leave = null;
        if ($employee) {
            $leave = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'Approved')
                ->where('from_date', '<=', $date)
                ->where('to_date', '>=', $date)
                ->first();
        }

        if ($hoursWorked <= 0 || $timeIn === '-' || $timeOut === '-') {
            if ($leave) {
                $typeMap = [
                    'vacation_leave' => 'Vacation Leave',
                    'sick_leave' => 'Sick Leave',
                    'paternity_leave' => 'Paternity Leave',
                    'solo_parent_leave' => 'Solo Parent Leave',
                    'bereavement_leave' => 'Bereavement Leave',
                    'vawc_leave' => 'VAWC Leave'
                ];
                return $typeMap[$leave->leave_type] ?? 'On Leave';
            }
            return 'Absent';
        }

        if ($hoursWorked > 0 && $hoursWorked < 5.0) {
            return 'Half Day';
        }
        if ($this->calculateLatenessMinutes($timeIn, $employeeIdNumber) > 0) {
            return 'Late';
        }

        return 'Present';
    }
}
