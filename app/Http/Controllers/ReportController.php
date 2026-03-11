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
        $service = new \App\Services\AttendanceReportService();
        return response()->json($service->getAnnualReport($year));
    }

    public function monthlyDepartment(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $service = new \App\Services\AttendanceReportService();
        return response()->json($service->getMonthlyDepartmentReport($year, $month));
    }

    public function dailyDepartment(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $service = new \App\Services\AttendanceReportService();
        return response()->json($service->getDailyDepartmentReport($date));
    }

    public function exportExcel(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        $filename = "HQ_Attendance_Report_{$year}.xlsx";
        
        \App\Utils\AuditLogger::log('Attendance', 'Exported', "Exported HQ Attendance Report for year {$year}.");
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceReportsExport($year, $month),
            $filename
        );
    }
}
