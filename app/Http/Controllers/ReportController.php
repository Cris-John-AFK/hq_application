<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get Annual Report Data (Mocked)
     * Matches user's Image 1 columns
     */
    public function annualAttendance(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        // Mock Data Structure
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June', 
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $data = [];
        
        foreach ($months as $index => $month) {
            // Logic to simulate realistic looking data or empty if future
            $isPast = $year < date('Y') || ($year == date('Y') && $index <= date('n') - 1);
            
            if ($isPast) {
                $headcount = 16;
                $workingDays = 22;
                $presentDays = rand(300, 340); // aggregate of all employees
                $absentDays = ($headcount * $workingDays) - $presentDays;
                
                $attendanceRate = round(($presentDays / ($headcount * $workingDays)) * 100, 2);
                $absenteeismRate = round(100 - $attendanceRate, 2);
                
                $data[] = [
                    'month' => $month,
                    'headcount' => $headcount,
                    'total_present_days' => $presentDays,
                    'total_working_days' => $headcount * $workingDays,
                    'attendance_rate' => $attendanceRate . '%',
                    'total_absent_days' => $absentDays,
                    'absenteeism_rate' => $absenteeismRate . '%',
                    'employees_late' => rand(0, 5),
                    'tardiness_frequency' => rand(0, 2) . '%',
                    'employees_undertime' => rand(0, 2),
                    'total_undertime_mins' => rand(0, 120),
                    'undertime_frequency' => rand(0, 1) . '%'
                ];
            } else {
                // Future months empty structure
                $data[] = [
                    'month' => $month,
                    'headcount' => 0,
                    'total_present_days' => 0,
                    'total_working_days' => 0,
                    'attendance_rate' => '#DIV/0!',
                    'total_absent_days' => 0,
                    'absenteeism_rate' => '#DIV/0!',
                    'employees_late' => 0,
                    'tardiness_frequency' => '#DIV/0!',
                    'employees_undertime' => 0,
                    'total_undertime_mins' => 0,
                    'undertime_frequency' => '#DIV/0!'
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * Get Monthly Department Report Data (Mocked)
     * Matches user's Image 2 columns
     */
    public function monthlyDepartment(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $departments = [
            'Production', 'Quality Control', 'Embroidery', 'Packing', 
            'Warehouse', 'Cutting', 'PPIC', 'PRD', 'Sample Line', 
            'Shipping', 'Maintenance & Engineering', 'HR & Admin', 'Accounting'
        ];

        $data = [];

        foreach ($departments as $dept) {
            // Mock realistic values
            $employees = rand(3, 15);
            $workingDays = 22;
            $scheduledHours = $employees * $workingDays * 8;
            $actualHours = round($scheduledHours * (rand(90, 100) / 100), 2);
            $regularHours = round($actualHours * 0.9, 2); // Regular is distinct from Total
            $otHours = rand(0, 20);
            $excessHours = rand(0, 5);
            
            $avgDailyHours = round($actualHours / ($employees * $workingDays), 2);

            $data[] = [
                'department' => $dept,
                'total_employees' => $employees,
                'total_working_days' => $workingDays * $employees, // aggregate days
                'total_scheduled_hours' => $scheduledHours,
                'total_actual_hours' => $actualHours,
                'regular_actual_hours' => $regularHours,
                'overtime_actual_hours' => $otHours,
                'excess_hours_worked' => $excessHours,
                'employees_with_excess_ot' => rand(0, 2),
                'avg_daily_working_hours' => $avgDailyHours
            ];
        }

        // Add 'Total' row logic in frontend or here if needed, usually frontend calculates totals
        return response()->json($data);
    }
}
