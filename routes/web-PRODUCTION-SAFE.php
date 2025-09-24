<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DailyUpdateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\TimesheetCalendarController;

/*
|--------------------------------------------------------------------------
| Web Routes (Production Safe)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// Backward compatibility for auth scaffolds
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Protected routes (session auth)
Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/worktime-data', [DashboardController::class, 'getWorktimeData'])->name('dashboard.worktime-data');

    // Users (use web-returning methods) - Now with proper middleware
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [AdminUserController::class, 'indexPage'])->name('users.index');
        Route::get('/users/{id}', [AdminUserController::class, 'showPage'])->name('users.show');
        Route::get('/users/{id}/edit', [AdminUserController::class, 'editPage'])->name('users.edit');
        Route::post('/users/{id}', [AdminUserController::class, 'updatePage'])->name('users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroyPage'])->name('users.destroy');
        Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset_password');
    });

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

    // Daily Update (employee)
    Route::get('/daily-update', [DailyUpdateController::class, 'index'])->name('daily-update.index');
    Route::post('/daily-update', [DailyUpdateController::class, 'store'])->name('daily-update.store');
    Route::get('/daily-update/api', [DailyUpdateController::class, 'api'])->name('daily-update.api');
    Route::post('/api/refresh-daily-update', [DailyUpdateController::class, 'refreshData'])->name('daily-update.refresh');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin-only routes with proper middleware
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // Admin Dashboard
    Route::get('/', function () { return view('admin.dashboard'); })->name('admin.dashboard');
    
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
    Route::get('/employees/time/export', function() {
        return response()->download(storage_path('app/exports/employee_time.csv'));
    })->name('admin.employees.time.export');
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

    // Admin Quick Actions (for Alaga sidebar)
    Route::get('/system/status', function() {
        return response()->json([
            'success' => true,
            'status' => 'All systems operational',
            'uptime' => '99.9%',
            'active_users' => \App\Models\User::whereNotNull('email_verified_at')->count(),
            'pending_timesheets' => \App\Models\Timesheet::where('status', 'submitted')->count(),
        ]);
    })->name('admin.system.status');

    Route::get('/reports/quick', function() {
        return response()->json([
            'success' => true,
            'report_url' => route('admin.timesheet.calendar'),
            'message' => 'Redirecting to timesheet calendar'
        ]);
    })->name('admin.reports.quick');
});

// DEBUG ROUTES - ONLY ENABLE IN LOCAL ENVIRONMENT
if (app()->environment('local')) {
    Route::middleware(['auth'])->group(function () {
        Route::get('/debug/timesheet', function() {
            $user = auth()->user();
            $timesheets = App\Models\Timesheet::where('user_id', $user->id)->get();
            
            return response()->json([
                'user' => $user->name,
                'user_id' => $user->id,
                'total_records' => $timesheets->count(),
                'recent_records' => $timesheets->take(5)->toArray(),
            ], 200, [], JSON_PRETTY_PRINT);
        })->name('debug.timesheet');
        
        Route::get('/debug/admin', function() {
            $user = auth()->user();
            
            // Only allow admin users to access admin debug info
            if (!$user->role || $user->role->name !== 'admin') {
                return response()->json(['error' => 'Admin access required'], 403);
            }
            
            return response()->json([
                'current_user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role ? $user->role->name : 'no role',
                    'is_admin' => $user->role && $user->role->name === 'admin'
                ],
                'user_count' => \App\Models\User::count(),
                'timesheet_count' => \App\Models\Timesheet::count(),
            ], 200, [], JSON_PRETTY_PRINT);
        })->middleware('admin')->name('debug.admin');

        Route::get('/debug/auth-status', function() {
            $user = auth()->user();
            return response()->json([
                'authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'user_email' => $user->email ?? null,
                'user_role' => $user && $user->role ? $user->role->name : 'No Role',
                'current_time' => now()->toDateTimeString(),
            ]);
        })->name('debug.auth');
    });
}

// Keep Breeze/Fortify auth routes (login, logout, etc.) if present
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}