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
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\LeaveController;
use App\Models\Timesheet;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Routes accessible without authentication (e.g., login, login, password reset).
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Keep Breeze/Fortify auth routes (login, logout, etc.) if present
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}

/*
|--------------------------------------------------------------------------
| Authenticated Employee Routes
|--------------------------------------------------------------------------
| Routes for general users requiring authentication. Grouped by feature.
*/
Route::middleware(['auth'])->group(function () {

    // --- 1. DASHBOARD ---
    // FIX: Intercept the main dashboard route to redirect admins to their specific area.
    Route::get('/dashboard', function () {
        if (Auth::user() && Auth::user()->role_id == 1) { // Assuming role_id 1 is 'admin'
            return redirect()->route('admin.dashboard');
        }
        return (new DashboardController)->index(request());
    })->name('dashboard');

    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/worktime-data', [DashboardController::class, 'getWorktimeData'])->name('dashboard.worktime-data');

    // --- 2. ATTENDANCE & CLOCKING ---
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'indexPage'])->name('attendance.index');
        Route::get('/data', [AttendanceController::class, 'getData'])->name('attendance.data');
        Route::get('/{id}', [AttendanceController::class, 'showPage'])->name('attendance.show');

        // Clocking Endpoints (Used by JS)
        Route::get('/status', [AttendanceController::class, 'status'])->name('attendance.status');
        Route::get('/calendar', [AttendanceController::class, 'calendar'])->name('attendance.calendar');
        Route::post('/clock-in', [AttendanceController::class, 'clock_in'])->name('attendance.clockin');
        Route::post('/clock-out', [AttendanceController::class, 'clock_out'])->name('attendance.clockout');
    });

    // --- 3. TIMESHEET (Employee CRUD & Summary) ---
    Route::prefix('timesheet')->group(function () {
        Route::get('/', [TimesheetController::class, 'index'])->name('timesheet.index');
        Route::get('/summary', [TimesheetController::class, 'getSummaryData'])->name('timesheet.summary');
        Route::get('/export', [TimesheetController::class, 'export'])->name('timesheet.export');
        Route::get('/load-more', [TimesheetController::class, 'loadMore'])->name('timesheet.loadMore');
        Route::get('/day-tasks', [TimesheetController::class, 'dayTasks'])->name('timesheet.dayTasks');
        
        // API/Action Routes
        Route::post('/', [TimesheetController::class, 'store'])->name('timesheet.store');
        Route::post('/store-or-update', [TimesheetController::class, 'storeOrUpdate'])->name('timesheet.storeOrUpdate');
        Route::delete('/{id}', [TimesheetController::class, 'destroy'])->name('timesheet.destroy');
    });
    
    // --- 4. DAILY UPDATE ---
    Route::prefix('daily-update')->group(function () {
        Route::get('/', [DailyUpdateController::class, 'index'])->name('daily-update.index');
        Route::post('/', [DailyUpdateController::class, 'store'])->name('daily-update.store');
        Route::get('/api', [DailyUpdateController::class, 'api'])->name('daily-update.api');
        Route::post('/refresh', [DailyUpdateController::class, 'refreshData'])->name('daily-update.refresh');
    });

    // --- 5. PROFILE & SELF-MANAGEMENT ---
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
        Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // --- 6. USER VIEWS & ACTIONS (General) ---
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'indexPage'])->name('users.index');
        Route::get('/{id}', [UserController::class, 'showPage'])->name('users.show');
        Route::get('/{id}/edit', [UserController::class, 'editPage'])->name('users.edit');

        Route::post('/{id}', [UserController::class, 'updatePage'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroyPage'])->name('users.destroy');
        
        // Admin-specific action defined under the user group but protected by 'can:admin'
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])
            ->middleware('can:admin')
            ->name('users.reset_password');
    });

    // Debug routes removed for security - only enable in development environment
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| All routes under this group require 'auth' and 'can:admin' middleware.
*/
Route::prefix('admin')->middleware(['auth', 'can:admin'])->group(function () {
    
    // Admin Dashboard & System Info
    Route::get('/', function () { return view('admin.dashboard'); })->name('admin.dashboard');
    Route::get('/quick-reports', [SystemController::class, 'quickReports'])->name('admin.quick.reports');
    Route::get('/system-status', [SystemController::class, 'status'])->name('admin.system.status');
    Route::get('/test', function () { return view('admin-dashboard-test'); })->name('admin.dashboard.test');
    
    // Admin User Management
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/data', [AdminUserController::class, 'getData'])->name('data');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset_password');
        Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy');
        Route::get('/role-distribution', [AdminUserController::class, 'getRoleDistribution'])->name('roleDistribution');
    });
    
    // Employee Time Overview
    Route::prefix('employees/time')->name('admin.employees.time.')->group(function () {
        Route::get('/', [AdminUserController::class, 'employeeTimeOverview'])->name('index');
        Route::get('/view', function () { return view('admin.employees_time'); })->name('view');
        Route::get('/records', [AdminUserController::class, 'getEmployeeRecords'])->name('records');
        Route::get('/summary', [AdminUserController::class, 'getWorkingHoursSummary'])->name('summary');
        Route::get('/chart', [AdminUserController::class, 'getTimeChartData'])->name('chart');
        Route::get('/{id}/details', [AdminUserController::class, 'getTimeDetails'])->name('details');
        Route::get('/export', [AdminUserController::class, 'exportTimeCsv'])->name('export');
    });

    // Admin Timesheets Management
    Route::get('/timesheets', [TimesheetController::class, 'adminIndex'])->name('timesheet.admin.index');

    // Leave Requests (Admin Approval)
    Route::prefix('leave')->name('admin.leave.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/list', [LeaveController::class, 'list'])->name('list');
        Route::post('/{id}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeaveController::class, 'reject'])->name('reject');
    });
});
