<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\EnhancedTimesheetController;
use App\Http\Controllers\UnifiedDashboardController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login','login');
    Route::post('/logout','logout')->middleware('auth:sanctum');
    Route::post('/reset-password', 'resetPassword')->middleware('auth:sanctum') ;
});

Route::middleware('auth:sanctum')->controller(UserController::class)->group(function(){
    Route::get('/user', 'index');
    Route::get('/user/{id}','show');
    Route::put('/user/{id}','update');
    Route::delete('/user/{id}','destroy');
});

// Legacy attendance routes (single session per day)
Route::middleware('auth:sanctum')->controller(AttendanceController::class)->group(function(){
    Route::post('/attendance/clock-in', 'clock_in');
    Route::post('/attendance/clock-out', 'clock_out');  
    Route::get('/attendance/report/{id}', 'report');
    Route::get('/attendance/all-report', 'all_report');
    Route::get('/attendance/status', 'status');
});

// Enhanced attendance sessions (multiple sessions per day)
Route::middleware('auth:sanctum')->controller(AttendanceSessionController::class)->group(function(){
    Route::post('/attendance-sessions/clock-in', 'clockIn');
    Route::post('/attendance-sessions/clock-out', 'clockOut');
    Route::get('/attendance-sessions/status', 'status');
    Route::get('/attendance-sessions/timeline/{userId?}', 'timeline');
    Route::post('/attendance-sessions/work-summary', 'addWorkSummary');
    Route::post('/attendance-sessions/auto-clock-out', 'autoClockOut');
});

// Enhanced timesheet with task merging and inconsistency detection
Route::middleware('auth:sanctum')->controller(EnhancedTimesheetController::class)->group(function(){
    Route::get('/timesheet/enhanced', 'index');
    Route::post('/timesheet/enhanced', 'store');
    Route::get('/timesheet/inconsistencies', 'getInconsistencies');
    Route::post('/timesheet/merge-duplicates', 'mergeDuplicateTasks');
});

// Unified dashboard with comprehensive data from all sources
Route::middleware('auth:sanctum')->controller(UnifiedDashboardController::class)->group(function(){
    Route::get('/dashboard/unified', 'dashboard');
    Route::get('/dashboard/timeline', 'timeline');
});
