<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DailyUpdateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\TimesheetCalendarController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\LeaveController;

/*
|--------------------------------------------------------------------------
| Web Routes (Blade views after login)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// Backward compatibility: some auth scaffolds redirect to /home after login
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Protected routes (session auth)
Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/worktime-data', [DashboardController::class, 'getWorktimeData'])->name('dashboard.worktime-data');

    // Users (use web-returning methods)
    Route::get('/users', [UserController::class, 'indexPage'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'showPage'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'editPage'])->name('users.edit');
    Route::post('/users/{id}', [UserController::class, 'updatePage'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroyPage'])->name('users.destroy');
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])
        ->middleware('can:admin')
        ->name('users.reset_password');

    // Attendance (web views)
    Route::get('/attendance', [AttendanceController::class, 'indexPage'])->name('attendance.index');
    Route::get('/attendance/data', [AttendanceController::class, 'getData'])->name('attendance.data');
    Route::get('/attendance/{id}', [AttendanceController::class, 'showPage'])->name('attendance.show');
    Route::get('/attendance/status', [AttendanceController::class, 'status'])->name('attendance.status');
    Route::get('/attendance/calendar', [AttendanceController::class, 'calendar'])->name('attendance.calendar');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clock_in'])->name('attendance.clockin');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clock_out'])->name('attendance.clockout');

    // Timesheet (employee)
    Route::get('/timesheet', [TimesheetController::class, 'index'])->name('timesheet.index');
    Route::post('/timesheet', [TimesheetController::class, 'store'])->name('timesheet.store');
    Route::post('/timesheet/store-or-update', [TimesheetController::class, 'storeOrUpdate'])->name('timesheet.storeOrUpdate');
    Route::delete('/timesheet/{id}', [TimesheetController::class, 'destroy'])->name('timesheet.destroy');
    Route::get('/timesheet/summary', [TimesheetController::class, 'getSummaryData'])->name('timesheet.summary');
    Route::get('/timesheet/export', [TimesheetController::class, 'export'])->name('timesheet.export');
    Route::get('/timesheet/load-more', [TimesheetController::class, 'loadMore'])->name('timesheet.loadMore');
    Route::get('/timesheet/day-tasks', [TimesheetController::class, 'dayTasks'])->name('timesheet.dayTasks');
    
    // Debug route for timesheet data
    Route::get('/debug/timesheet', function() {
        $user = auth()->user();
        $timesheets = App\Models\Timesheet::where('user_id', $user->id)->get();
        
        return response()->json([
            'user' => $user->name,
            'user_id' => $user->id,
            'total_records' => $timesheets->count(),
            'recent_records' => $timesheets->take(5)->toArray(),
            'all_records' => $timesheets->toArray()
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.timesheet');
    
    // Debug route for admin data
    Route::get('/debug/admin', function() {
        $user = auth()->user();
        $allUsers = App\Models\User::with('role')->get();
        $allTimesheets = App\Models\Timesheet::with('user')->get();
        
        return response()->json([
            'current_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role ? $user->role->name : 'no role',
                'is_admin' => $user->role && $user->role->name === 'admin'
            ],
            'all_users' => $allUsers->toArray(),
            'timesheet_summary' => [
                'total_records' => $allTimesheets->count(),
                'by_user' => $allTimesheets->groupBy('user_id')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'user' => $group->first()->user->name ?? 'Unknown'
                    ];
                })
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.admin');

    // Daily Update (employee)
    Route::get('/daily-update', [DailyUpdateController::class, 'index'])->name('daily-update.index');
    Route::post('/daily-update', [DailyUpdateController::class, 'store'])->name('daily-update.store');
    Route::get('/daily-update/api', [DailyUpdateController::class, 'api'])->name('daily-update.api');
    Route::post('/api/refresh-daily-update', [DailyUpdateController::class, 'refreshData'])->name('daily-update.refresh');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // DEBUG: Test attendance status authentication
    Route::get('/debug/auth-status', function() {
        $user = auth()->user();
        $response = [
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_email' => $user->email ?? null,
            'user_role' => $user && $user->role ? $user->role->name : 'No Role',
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'current_time' => now()->toDateTimeString(),
            'middleware_applied' => 'auth middleware working',
        ];
        
        // Also test the attendance status directly
        if (auth()->check()) {
            try {
                $attendanceController = new \App\Http\Controllers\AttendanceController();
                $attendanceStatus = $attendanceController->status(request());
                $response['attendance_status_test'] = json_decode($attendanceStatus->getContent(), true);
                $response['attendance_status_code'] = $attendanceStatus->getStatusCode();
            } catch (\Exception $e) {
                $response['attendance_status_error'] = $e->getMessage();
            }
        }
        
        return response()->json($response);
    })->name('debug.auth');
    
    // EMERGENCY DEBUG ROUTE - Remove after fixing leave issues
    Route::get('/emergency-fix/{userId?}', function($userId = null) {
        if (!$userId) $userId = Auth::id();
        
        $user = App\Models\User::findOrFail($userId);
        
        // Force refresh all user data
        $user->refresh();
        
        // Clear user-specific caches if any exist
        if (Cache::has("user_{$userId}_data")) {
            Cache::forget("user_{$userId}_data");
        }
        
        // Get fresh attendance data  
        $recentAttendance = $user->attendances()
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();
        
        // Get fresh timesheet data
        $recentTimesheet = $user->timesheets()
            ->where('date', '>=', now()->subDays(30)) 
            ->orderBy('date', 'desc')
            ->get();
        
        // Get fresh work summaries
        $recentSummaries = $user->workSummaries()
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc') 
            ->get();
        
        $debug = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role->name ?? 'No Role',
            'attendance_records' => $recentAttendance->count(),
            'timesheet_records' => $recentTimesheet->count(), 
            'work_summaries' => $recentSummaries->count(),
            'latest_attendance' => $recentAttendance->first()?->date,
            'latest_timesheet' => $recentTimesheet->first()?->date,
            'system_time' => now()->toDateTimeString(),
            'session_data' => array_keys(session()->all()),
            'cache_status' => 'cleared',
        ];
        
        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    })->middleware('auth')->name('emergency.debug');
});

// Admin-only routes with proper prefix and middleware
Route::prefix('admin')->middleware(['auth', 'can:admin'])->group(function () {
    // Admin Dashboard
    Route::get('/', function () { return view('admin.dashboard'); })->name('admin.dashboard');
    
    // Admin Dashboard Debug Test (temporary)
    Route::get('/test', function () { return view('admin-dashboard-test'); })->name('admin.dashboard.test');
    
    // Admin User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/data', [AdminUserController::class, 'getData'])->name('admin.users.data');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset_password');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/users/role-distribution', [AdminUserController::class, 'getRoleDistribution'])->name('admin.users.roleDistribution');
    
    // Employee Time Overview
    Route::get('/employees/time', [AdminUserController::class, 'employeeTimeOverview'])->name('admin.employees.time');
    Route::get('/employees/time/chart', [AdminUserController::class, 'getTimeChartData'])->name('admin.employees.time.chart');
    Route::get('/employees/time/{id}/details', [AdminUserController::class, 'getTimeDetails'])->name('admin.employees.time.details');
    Route::get('/employees/time/export', [AdminUserController::class, 'exportTimeCsv'])->name('admin.employees.time.export');
    
    // Employee Time Overview View
    Route::get('/employees/time/view', function () { return view('admin.employees_time'); })->name('admin.employees.time.view');
    
    // Admin Timesheets
    Route::get('/timesheets', [TimesheetController::class, 'adminIndex'])->name('timesheet.admin.index');
    
    // Admin Timesheet Calendar
    Route::get('/timesheet-calendar', [TimesheetCalendarController::class, 'index'])->name('admin.timesheet.calendar');
    Route::get('/timesheet-calendar/data', [TimesheetCalendarController::class, 'getCalendarData'])->name('admin.timesheet.calendar.data');
    Route::get('/timesheet-calendar/day/{date}', [TimesheetCalendarController::class, 'getDayDetails'])->name('admin.timesheet.calendar.day');
    Route::get('/timesheet-calendar/stats', [TimesheetCalendarController::class, 'getStatistics'])->name('admin.timesheet.calendar.stats');
    Route::post('/timesheet-calendar/approve/{id}', [TimesheetCalendarController::class, 'approveTimesheet'])->name('admin.timesheet.calendar.approve');
    Route::post('/timesheet-calendar/reject/{id}', [TimesheetCalendarController::class, 'rejectTimesheet'])->name('admin.timesheet.calendar.reject');
    Route::post('/timesheet-calendar/bulk-approve', [TimesheetCalendarController::class, 'bulkApprove'])->name('admin.timesheet.calendar.bulkApprove');
    Route::post('/timesheet-calendar/bulk-reject', [TimesheetCalendarController::class, 'bulkReject'])->name('admin.timesheet.calendar.bulkReject');
    Route::get('/timesheet-calendar/export', [TimesheetCalendarController::class, 'export'])->name('admin.timesheet.calendar.export');

    // System utilities (live data for sidebar buttons)
    Route::get('/system-status', [SystemController::class, 'status'])->name('admin.system.status');
    Route::get('/quick-reports', [SystemController::class, 'quickReports'])->name('admin.quick.reports');

    // Leave Requests (Admin)
    Route::get('/leave', [LeaveController::class, 'index'])->name('admin.leave.index');
    Route::get('/leave/list', [LeaveController::class, 'list'])->name('admin.leave.list');
    Route::post('/leave/{id}/approve', [LeaveController::class, 'approve'])->name('admin.leave.approve');
    Route::post('/leave/{id}/reject', [LeaveController::class, 'reject'])->name('admin.leave.reject');
});

// Keep Breeze/Fortify auth routes (login, logout, etc.) if present
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
