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

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/api/user', [AuthController::class, 'user']);
    Route::post('/api/user/avatar', [\App\Http\Controllers\UserController::class, 'uploadAvatar']);
    Route::put('/api/user', [\App\Http\Controllers\UserController::class, 'update']);
    Route::get('/api/users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::post('/api/users', [\App\Http\Controllers\UserController::class, 'store']);
    Route::put('/api/users/{id}', [\App\Http\Controllers\UserController::class, 'updateEmployee']);
    Route::put('/api/users/{id}', [\App\Http\Controllers\UserController::class, 'updateEmployee']);
    Route::put('/api/users/{id}/password', [\App\Http\Controllers\UserController::class, 'changeUserPassword']);
    Route::post('/api/users/bulk-credits', [\App\Http\Controllers\UserController::class, 'bulkAddCredits']);
    Route::post('/api/users/reset-all-credits', [\App\Http\Controllers\UserController::class, 'resetAllCredits']);
    
    // Departments
    Route::get('/api/departments', [\App\Http\Controllers\DepartmentController::class, 'index']);
    Route::post('/api/departments', [\App\Http\Controllers\DepartmentController::class, 'store']);
    Route::get('/api/departments/stats', [\App\Http\Controllers\DepartmentController::class, 'getStats']);

    // Reports (Mocked)
    Route::get('/api/reports/annual', [\App\Http\Controllers\ReportController::class, 'annualAttendance']);
    Route::get('/api/reports/monthly-department', [\App\Http\Controllers\ReportController::class, 'monthlyDepartment']);
    
    // Leave Routes
    Route::get('/api/leave-requests/export', [\App\Http\Controllers\LeaveRequestController::class, 'export']);
    Route::get('/api/leave-stats', [\App\Http\Controllers\LeaveRequestController::class, 'stats']);
    
    // Decision Support Routes
    Route::get('/api/leave-requests/{id}/analysis', [\App\Http\Controllers\LeaveRequestController::class, 'getAnalysis']);
    Route::get('/api/users/{id}/forecast', [\App\Http\Controllers\LeaveRequestController::class, 'getUserForecast']);
    
    Route::get('/api/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'index']);
    Route::get('/api/calendar-events', [\App\Http\Controllers\LeaveRequestController::class, 'calendarEvents']);
    // Custom Events
    Route::get('/api/custom-events', [\App\Http\Controllers\CalendarEventController::class, 'index']);
    Route::post('/api/custom-events', [\App\Http\Controllers\CalendarEventController::class, 'store']);
    Route::post('/api/custom-events/mark-read', [\App\Http\Controllers\CalendarEventController::class, 'markAsRead']);
    Route::delete('/api/custom-events/{id}', [\App\Http\Controllers\CalendarEventController::class, 'destroy']);
    
    Route::post('/api/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'store']);
    Route::put('/api/leave-requests/{leaveRequest}', [\App\Http\Controllers\LeaveRequestController::class, 'update']);
    Route::get('/api/users/{id}/leave-history', [\App\Http\Controllers\LeaveRequestController::class, 'userHistory']);
    
    // System Logs
    Route::get('/api/system-logs', [\App\Http\Controllers\SystemLogsController::class, 'index']);

    // Notifications
    Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/api/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::put('/api/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::delete('/api/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);

    // Announcements
    Route::get('/api/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index']);
    Route::get('/api/announcements/all', [\App\Http\Controllers\AnnouncementController::class, 'all']);
    Route::post('/api/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store']);
    Route::put('/api/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'update']);
    Route::delete('/api/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'destroy']);
    Route::patch('/api/announcements/{id}/toggle', [\App\Http\Controllers\AnnouncementController::class, 'toggle']);

    // Catch-all meant for Vue Router
    Route::get('/{any}', function () {
        return response()
            ->view('dashboard')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    })->where('any', '.*');
});