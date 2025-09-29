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

    // --- 7. DEBUG & EMERGENCY ROUTES (FOR DEVELOPMENT ONLY) ---
    // These routes should be removed or commented out for production deployment.
    Route::prefix('debug')->group(function () {
        Route::get('/auth-status', function() {
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

        Route::get('/timesheet', function() {
            $user = auth()->user();
            $timesheets = Timesheet::where('user_id', $user->id)->get();
            
            return response()->json([
                'user' => $user->name,
                'user_id' => $user->id,
                'total_records' => $timesheets->count(),
                'recent_records' => $timesheets->take(5)->toArray(),
                'all_records' => $timesheets->toArray()
            ], 200, [], JSON_PRETTY_PRINT);
        })->name('debug.timesheet');
        
        Route::get('/admin', function() {
            $user = auth()->user();
            $allUsers = User::with('role')->get();
            $allTimesheets = Timesheet::with('user')->get();
            
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

        // EMERGENCY DEBUG ROUTE - Useful for clearing user-specific caches
        Route::get('/emergency-fix/{userId?}', function($userId = null) {
            if (!$userId) $userId = Auth::id();
            
            $user = User::findOrFail($userId);
            
            $user->refresh();
            
            if (Cache::has("user_{$userId}_data")) {
                Cache::forget("user_{$userId}_data");
            }
            
            $recentAttendance = $user->attendances()
                ->where('date', '>=', now()->subDays(30))
                ->orderBy('date', 'desc')
                ->get();
            
            $recentTimesheet = $user->timesheets()
                ->where('date', '>=', now()->subDays(30)) 
                ->orderBy('date', 'desc') 
                ->get();
            
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
        })->name('emergency.debug');
    });
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
        Route::get('/chart', [AdminUserController::class, 'getTimeChartData'])->name('chart');
        Route::get('/{id}/details', [AdminUserController::class, 'getTimeDetails'])->name('details');
        Route::get('/export', [AdminUserController::class, 'exportTimeCsv'])->name('export');
    });

    // Admin Timesheets & Calendar Management
    Route::get('/timesheets', [TimesheetController::class, 'adminIndex'])->name('timesheet.admin.index');

    // FIX: Simplified the name() definition here.
    Route::prefix('timesheet-calendar')->name('admin.timesheet.calendar')->group(function () { 
        Route::get('/', [TimesheetCalendarController::class, 'index'])->name(''); // This will now be named 'admin.timesheet.calendar'
        Route::get('/data', [TimesheetCalendarController::class, 'getCalendarData'])->name('.data');
        Route::get('/day/{date}', [TimesheetCalendarController::class, 'getDayDetails'])->name('.day');
        Route::get('/stats', [TimesheetCalendarController::class, 'getStatistics'])->name('.stats');
        Route::post('/approve/{id}', [TimesheetCalendarController::class, 'approveTimesheet'])->name('.approve');
        Route::post('/reject/{id}', [TimesheetCalendarController::class, 'rejectTimesheet'])->name('.reject');
        Route::post('/bulk-approve', [TimesheetCalendarController::class, 'bulkApprove'])->name('.bulkApprove');
        Route::post('/bulk-reject', [TimesheetCalendarController::class, 'bulkReject'])->name('.bulkReject');
        Route::get('/export', [TimesheetCalendarController::class, 'export'])->name('.export');
    });

    // Leave Requests (Admin Approval)
    Route::prefix('leave')->name('admin.leave.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/list', [LeaveController::class, 'list'])->name('list');
        Route::post('/{id}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeaveController::class, 'reject'])->name('reject');
    });
});
