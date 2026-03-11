<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vue', function () {
    return view('vue');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Allow logging expiry even if unauthenticated (called when session dies)
Route::post('/api/log-expiry', [AuthController::class, 'logExpiry']);

// Employee Portal (Protected with Throttling)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/api/employee-portal/login', [\App\Http\Controllers\EmployeePortalController::class, 'login']);
    Route::post('/api/employee-portal/submit-leave', [\App\Http\Controllers\EmployeePortalController::class, 'submitLeave']);
    Route::put('/api/employee-portal/update-leave/{id}', [\App\Http\Controllers\EmployeePortalController::class, 'updateLeave']);
    Route::put('/api/employee-portal/archive-leave/{id}', [\App\Http\Controllers\EmployeePortalController::class, 'archiveLeave']);
});

// Serve the app shell for the employee portal route without strictly matching auth middleware
Route::get('/portal', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
});

Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/api/user', [AuthController::class, 'user']);
    Route::get('/api/omni-search', [\App\Http\Controllers\OmniSearchController::class, 'search'])->middleware('throttle:120,1');
    Route::post('/api/user/avatar', [\App\Http\Controllers\UserController::class, 'uploadAvatar']);
    Route::put('/api/user', [\App\Http\Controllers\UserController::class, 'update']);
    Route::post('/api/user/change-password', [\App\Http\Controllers\UserController::class, 'changeOwnPassword']);
    // Admin Only Functional Routes
    Route::middleware('admin')->group(function () {
        Route::get('/api/users', [\App\Http\Controllers\UserController::class, 'index']);
        Route::post('/api/users', [\App\Http\Controllers\UserController::class, 'store']);
        Route::put('/api/users/{id}', [\App\Http\Controllers\UserController::class, 'updateEmployee']);
        Route::put('/api/users/{id}/password', [\App\Http\Controllers\UserController::class, 'changeUserPassword']);
        Route::post('/api/users/bulk-credits', [\App\Http\Controllers\UserController::class, 'bulkAddCredits']);
        Route::post('/api/users/reset-all-credits', [\App\Http\Controllers\UserController::class, 'resetAllCredits']);
        Route::post('/api/users/create-from-employee', [\App\Http\Controllers\UserController::class, 'createFromEmployee']);

        Route::get('/api/employees/export', [\App\Http\Controllers\EmployeeController::class, 'export']);
        Route::post('/api/employees/import', [\App\Http\Controllers\EmployeeController::class, 'import']);
        Route::post('/api/employees', [\App\Http\Controllers\EmployeeController::class, 'store']);
        Route::put('/api/employees/{id}', [\App\Http\Controllers\EmployeeController::class, 'update']);
        Route::delete('/api/employees/{id}', [\App\Http\Controllers\EmployeeController::class, 'destroy']);
        Route::post('/api/employees/{id}/archive', [\App\Http\Controllers\EmployeeController::class, 'archive']);
        Route::post('/api/employees/{id}/unarchive', [\App\Http\Controllers\EmployeeController::class, 'unarchive']);
        Route::post('/api/employees/{id}/adjust-leave', [\App\Http\Controllers\EmployeeController::class, 'adjustLeave']);
        Route::post('/api/employees/bulk-update-leaves', [\App\Http\Controllers\EmployeeController::class, 'bulkUpdateLeaves']);

        Route::post('/api/departments', [\App\Http\Controllers\DepartmentController::class, 'store']);

        Route::post('/api/custom-events', [\App\Http\Controllers\CalendarEventController::class, 'store']);
        Route::delete('/api/custom-events/{id}', [\App\Http\Controllers\CalendarEventController::class, 'destroy']);

        Route::get('/api/system-logs', [\App\Http\Controllers\SystemLogsController::class, 'index']);

        Route::post('/api/inventory/import', [\App\Http\Controllers\InventoryController::class, 'import']);
        Route::post('/api/inventory', [\App\Http\Controllers\InventoryController::class, 'store']);
        Route::put('/api/inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'update']);
        Route::delete('/api/inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'destroy']);

        Route::get('/api/announcements/all', [\App\Http\Controllers\AnnouncementController::class, 'all']);
        Route::post('/api/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store']);
        Route::put('/api/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'update']);
        Route::delete('/api/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'destroy']);
        Route::patch('/api/announcements/{id}/toggle', [\App\Http\Controllers\AnnouncementController::class, 'toggle']);

        Route::post('/api/leave-requests/{id}/archive', [\App\Http\Controllers\LeaveRequestController::class, 'archive'])->middleware('throttle:30,1');
        Route::post('/api/leave-requests/{id}/unarchive', [\App\Http\Controllers\LeaveRequestController::class, 'unarchive'])->middleware('throttle:30,1');
        Route::post('/api/leave-requests/bulk-archive', [\App\Http\Controllers\LeaveRequestController::class, 'bulkArchive'])->middleware('throttle:5,1');
        Route::post('/api/leave-requests/bulk-action', [\App\Http\Controllers\LeaveRequestController::class, 'bulkAction'])->middleware('throttle:15,1');
        Route::get('/api/leave-requests/archive-index', [\App\Http\Controllers\LeaveRequestController::class, 'getArchiveIndex']);
        Route::get('/api/attendance-records/dates', [\App\Http\Controllers\AttendanceController::class, 'availableDates']);
        Route::get('/api/attendance-records', [\App\Http\Controllers\AttendanceController::class, 'index']);
        Route::get('/api/attendance-stats/graph', [\App\Http\Controllers\AttendanceController::class, 'stats']);
        Route::post('/api/attendance-records/bulk', [\App\Http\Controllers\AttendanceController::class, 'bulkStore']);
        Route::get('/api/attendance-roster', [\App\Http\Controllers\AttendanceController::class, 'roster']);
        Route::get('/api/attendance-anomalies', [\App\Http\Controllers\AttendanceController::class, 'anomalies']);
        Route::get('/api/attendance-summary', [\App\Http\Controllers\AttendanceController::class, 'summary']);

        // Reports (Now under Admin only for security)
        Route::get('/api/reports/annual', [\App\Http\Controllers\ReportController::class, 'annualAttendance']);
        Route::get('/api/reports/monthly-department', [\App\Http\Controllers\ReportController::class, 'monthlyDepartment']);
        Route::get('/api/reports/daily-department', [\App\Http\Controllers\ReportController::class, 'dailyDepartment']);
        Route::get('/api/reports/attendance/export', [\App\Http\Controllers\ReportController::class, 'exportExcel']);
    });
});

Route::middleware(['auth', 'admin'])->put('/api/settings/{key}', [\App\Http\Controllers\SystemSettingsController::class, 'update']);
Route::get('/api/settings', [\App\Http\Controllers\SystemSettingsController::class, 'getAll']);
Route::get('/api/settings/{key}', [\App\Http\Controllers\SystemSettingsController::class, 'get']);

Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    // Share/Employee Access Routes (Read-only for most)
    Route::get('/api/employees/shift-stats', [\App\Http\Controllers\EmployeeController::class, 'getShiftStats']);
    Route::get('/api/employees', [\App\Http\Controllers\EmployeeController::class, 'index']);
    Route::get('/api/employees/{id}', [\App\Http\Controllers\EmployeeController::class, 'show']);
    Route::get('/api/employees/find-by-code/{id}', [\App\Http\Controllers\EmployeeController::class, 'findByEmployeeId']);

    Route::get('/api/departments', [\App\Http\Controllers\DepartmentController::class, 'index']);
    Route::get('/api/departments/stats', [\App\Http\Controllers\DepartmentController::class, 'getStats']);

    Route::get('/api/leave-requests/export', [\App\Http\Controllers\LeaveRequestController::class, 'export']);
    Route::get('/api/leave-stats', [\App\Http\Controllers\LeaveRequestController::class, 'stats']);
    Route::get('/api/leave-analytics', [\App\Http\Controllers\LeaveRequestController::class, 'analyticsData']);
    Route::get('/api/leave-analytics/export', [\App\Http\Controllers\LeaveRequestController::class, 'analyticsExport']);
    Route::get('/api/dept-head/report', [\App\Http\Controllers\LeaveRequestController::class, 'deptHeadReport']);
    Route::get('/api/dept-head/report/export', [\App\Http\Controllers\LeaveRequestController::class, 'deptHeadReportExport']);

    Route::get('/api/leave-requests/{id}/analysis', [\App\Http\Controllers\LeaveRequestController::class, 'getAnalysis']);
    Route::get('/api/leave-requests/{id}', [\App\Http\Controllers\LeaveRequestController::class, 'show']);
    Route::get('/api/users/{id}/forecast', [\App\Http\Controllers\LeaveRequestController::class, 'getUserForecast']);

    Route::get('/api/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'index']);
    Route::get('/api/calendar-events', [\App\Http\Controllers\LeaveRequestController::class, 'calendarEvents']);
    Route::get('/api/custom-events', [\App\Http\Controllers\CalendarEventController::class, 'index']);
    Route::post('/api/custom-events/mark-read', [\App\Http\Controllers\CalendarEventController::class, 'markAsRead']);

    Route::post('/api/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'store']);
    Route::put('/api/leave-requests/{leaveRequest}', [\App\Http\Controllers\LeaveRequestController::class, 'update']);
    Route::get('/api/users/{id}/leave-history', [\App\Http\Controllers\LeaveRequestController::class, 'userHistory']);

    Route::get('/api/inventory/export', [\App\Http\Controllers\InventoryController::class, 'export']);
    Route::get('/api/inventory', [\App\Http\Controllers\InventoryController::class, 'index']);
    Route::get('/api/inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'show']);

    Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/api/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::put('/api/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::delete('/api/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);

    Route::get('/api/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index']);

    // Catch-all meant for Vue Router
    Route::get('/{any}', function () {
        return response()
            ->view('dashboard')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    })->where('any', '.*');
});