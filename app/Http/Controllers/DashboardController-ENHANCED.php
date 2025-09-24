<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\Timesheet;
use App\Models\WorkSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        // Route to appropriate dashboard based on role
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }
        
        if ($user->isEmployee()) {
            return $this->employeeDashboard();
        }
        
        // Fallback for users without specific roles
        return $this->defaultDashboard();
    }

    /**
     * Admin dashboard with administrative overview
     */
    private function adminDashboard()
    {
        return view('dashboard-admin', [
            'userTheme' => 'admin-theme',
            'dashboardType' => 'admin'
        ]);
    }

    /**
     * Employee dashboard with personal work tracking
     */
    private function employeeDashboard()
    {
        return view('dashboard-employee', [
            'userTheme' => 'employee-theme', 
            'dashboardType' => 'employee'
        ]);
    }

    /**
     * Default dashboard for users without specific roles
     */
    private function defaultDashboard()
    {
        return view('dashboard', [
            'userTheme' => 'default-theme',
            'dashboardType' => 'default'
        ]);
    }

    /**
     * Get dashboard summary data (role-aware)
     */
    public function getData(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();
        
        try {
            if ($user->isAdmin()) {
                $summary = $this->getAdminSummaryData();
            } else {
                $summary = $this->getEmployeeSummaryData($userId);
            }
            
            return response()->json([
                'success' => true,
                'user_role' => $user->getRoleName(),
                'dashboard_type' => $user->isAdmin() ? 'admin' : 'employee',
                ...$summary
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin-specific dashboard data
     */
    private function getAdminSummaryData(): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        // Admin sees system-wide statistics
        $todayStats = $this->getSystemStatsForPeriod($today, $today);
        $weeklyStats = $this->getSystemStatsForPeriod($weekStart, $weekEnd);
        $monthlyStats = $this->getSystemStatsForPeriod($monthStart, $monthEnd);

        // Get user management stats
        $userStats = $this->getUserManagementStats();
        
        // Get system health metrics
        $systemHealth = $this->getSystemHealthMetrics();

        return [
            'today' => $todayStats['total_hours'],
            'weekly' => $weeklyStats['total_hours'],
            'monthly' => $monthlyStats['total_hours'],
            'tasks' => $todayStats['active_employees'],
            
            // Admin-specific data
            'admin_stats' => [
                'total_employees' => $userStats['total_employees'],
                'active_today' => $todayStats['active_employees'],
                'pending_timesheets' => $userStats['pending_timesheets'],
                'pending_approvals' => $userStats['pending_approvals'],
            ],
            
            'system_health' => $systemHealth,
            
            'period_stats' => [
                'today' => $todayStats,
                'weekly' => $weeklyStats,
                'monthly' => $monthlyStats,
            ]
        ];
    }

    /**
     * Get employee-specific dashboard data
     */
    private function getEmployeeSummaryData($userId): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        // Today's hours from timesheets
        $todayHours = Timesheet::where('user_id', $userId)
            ->whereDate('date', $today->toDateString())
            ->sum('hours_worked');
        if (!$todayHours) {
            $timesheets = Timesheet::where('user_id', $userId)
                ->whereDate('date', $today->toDateString())
                ->whereNotNull('hours')->get();
            foreach ($timesheets as $t) {
                $parts = explode(':', $t->hours);
                $todayHours += (int)$parts[0] + ((int)($parts[1] ?? 0) / 60);
            }
        }
        
        // Weekly hours from timesheets
        $weeklyHours = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->sum('hours_worked');
        if (!$weeklyHours) {
            $timesheets = Timesheet::where('user_id', $userId)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->whereNotNull('hours')->get();
            foreach ($timesheets as $t) {
                $parts = explode(':', $t->hours);
                $weeklyHours += (int)$parts[0] + ((int)($parts[1] ?? 0) / 60);
            }
        }
        
        // Monthly hours from timesheets
        $monthlyHours = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('hours_worked');
        if (!$monthlyHours) {
            $timesheets = Timesheet::where('user_id', $userId)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->whereNotNull('hours')->get();
            foreach ($timesheets as $t) {
                $parts = explode(':', $t->hours);
                $monthlyHours += (int)$parts[0] + ((int)($parts[1] ?? 0) / 60);
            }
        }
        
        // Tasks done (from timesheet entries)
        $tasksDone = Timesheet::where('user_id', $userId)
            ->whereDate('date', $today)
            ->count();

        // Get attendance summary
        $attendanceSummary = $this->getEmployeeAttendanceSummary($userId);
        
        // Get work patterns
        $workPatterns = $this->getEmployeeWorkPatterns($userId);
        
        return [
            'today' => round($todayHours ?: 0, 1),
            'weekly' => round($weeklyHours ?: 0, 1),
            'monthly' => round($monthlyHours ?: 0, 1),
            'tasks' => $tasksDone ?: 0,
            
            // Employee-specific data
            'employee_stats' => [
                'attendance_rate' => $attendanceSummary['attendance_rate'],
                'average_daily_hours' => $attendanceSummary['average_daily_hours'],
                'days_worked_this_month' => $attendanceSummary['days_worked_this_month'],
                'pending_timesheet_entries' => $tasksDone,
            ],
            
            'work_patterns' => $workPatterns,
            
            // Personal goals and progress
            'goals' => [
                'daily_target' => 8.0,
                'weekly_target' => 40.0,
                'monthly_target' => 160.0,
                'daily_progress' => min(($todayHours / 8.0) * 100, 100),
                'weekly_progress' => min(($weeklyHours / 40.0) * 100, 100),
                'monthly_progress' => min(($monthlyHours / 160.0) * 100, 100),
            ],
        ];
    }

    /**
     * Get work time data for charts (role-aware)
     */
    public function getWorktimeData(Request $request)
    {
        $period = $request->get('period', 'week');
        $userId = Auth::id();
        $user = Auth::user();
        
        try {
            if ($user->isAdmin()) {
                $data = $this->getAdminChartData($period);
                $summary = $this->getAdminSummaryData();
            } else {
                $data = $this->getEmployeeChartData($userId, $period);
                $summary = $this->getEmployeeSummaryData($userId);
            }
            
            return response()->json([
                'success' => true,
                'labels' => $data['labels'],
                'data' => $data['values'],
                'summary' => $summary,
                'chart_type' => $user->isAdmin() ? 'admin' : 'employee'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load work time data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system-wide statistics for admin (for a given period)
     */
    private function getSystemStatsForPeriod($startDate, $endDate): array
    {
        $totalHours = Timesheet::whereBetween('date', [$startDate, $endDate])
            ->sum('hours_worked');

        $activeEmployees = Timesheet::whereBetween('date', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $totalEntries = Timesheet::whereBetween('date', [$startDate, $endDate])
            ->count();

        return [
            'total_hours' => round($totalHours ?: 0, 1),
            'active_employees' => $activeEmployees,
            'total_entries' => $totalEntries,
            'average_hours_per_employee' => $activeEmployees > 0 ? round($totalHours / $activeEmployees, 1) : 0
        ];
    }

    /**
     * Get user management statistics for admin
     */
    private function getUserManagementStats(): array
    {
        return [
            'total_employees' => \App\Models\User::employees()->count(),
            'total_admins' => \App\Models\User::admins()->count(),
            'pending_timesheets' => Timesheet::where('status', 'submitted')->count(),
            'pending_approvals' => Timesheet::where('status', 'submitted')
                ->whereNull('approved_by')
                ->count(),
            'active_sessions' => \App\Models\Attendance::incomplete()->count(),
        ];
    }

    /**
     * Get system health metrics for admin dashboard
     */
    private function getSystemHealthMetrics(): array
    {
        return [
            'uptime' => '99.9%', // This would be calculated from actual system metrics
            'active_sessions' => \App\Models\Attendance::incomplete()->count(),
            'failed_logins_today' => 0, // Would track from logs
            'server_status' => 'healthy',
            'database_status' => 'connected',
            'last_backup' => now()->subHours(2)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get employee attendance summary
     */
    private function getEmployeeAttendanceSummary($userId): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $today = Carbon::today();

        $attendances = \App\Models\Attendance::where('user_id', $userId)
            ->whereBetween('date', [$monthStart, $today])
            ->whereNotNull('clock_in')
            ->get();

        $workingDays = $attendances->count();
        $totalMinutes = $attendances->sum('total_minutes');
        $averageDailyHours = $workingDays > 0 ? ($totalMinutes / $workingDays / 60) : 0;
        
        $expectedWorkingDays = $monthStart->diffInWeekdays($today) + 1;
        $attendanceRate = $expectedWorkingDays > 0 ? ($workingDays / $expectedWorkingDays) * 100 : 0;

        return [
            'days_worked_this_month' => $workingDays,
            'attendance_rate' => round($attendanceRate, 1),
            'average_daily_hours' => round($averageDailyHours, 1),
            'total_hours_this_month' => round($totalMinutes / 60, 1),
        ];
    }

    /**
     * Get employee work patterns
     */
    private function getEmployeeWorkPatterns($userId): array
    {
        $lastWeek = Carbon::now()->subWeek();
        $timesheets = Timesheet::where('user_id', $userId)
            ->where('date', '>=', $lastWeek)
            ->get();

        return [
            'most_productive_day' => $this->getMostProductiveDay($timesheets),
            'average_start_time' => $this->getAverageStartTime($timesheets),
            'work_consistency' => $this->getWorkConsistency($timesheets),
        ];
    }

    /**
     * Get admin chart data (system-wide)
     */
    private function getAdminChartData($period): array
    {
        // Implementation would show system-wide data across all employees
        // For now, return sample structure
        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'values' => [120, 135, 115, 140, 130, 45, 20] // Total hours across all employees
        ];
    }

    /**
     * Get employee chart data (personal)
     */
    private function getEmployeeChartData($userId, $period): array
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'day':
                return $this->getDailyData($userId, $now);
            case 'week':
                return $this->getWeeklyData($userId, $now);
            case 'month':
                return $this->getMonthlyData($userId, $now);
            case 'year':
                return $this->getYearlyData($userId, $now);
            default:
                return $this->getWeeklyData($userId, $now);
        }
    }

    // ... (keep existing chart data methods from original controller)
    
    /**
     * Get weekly data (7 days) - unchanged from original
     */
    private function getWeeklyData($userId, $date)
    {
        $labels = [];
        $values = [];
        
        $startOfWeek = $date->copy()->startOfWeek();
        
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            $labels[] = $currentDay->format('D');
            
            // Get total hours for this day from timesheets
            $dailyHours = Timesheet::where('user_id', $userId)
                ->whereDate('date', $currentDay->toDateString())
                ->sum('hours_worked');
            
            // Fallback to legacy hours format if no hours_worked
            if (!$dailyHours) {
                $timesheets = Timesheet::where('user_id', $userId)
                    ->whereDate('date', $currentDay->toDateString())
                    ->whereNotNull('hours')
                    ->get();
                    
                foreach ($timesheets as $timesheet) {
                    if ($timesheet->hours) {
                        $hoursParts = explode(':', $timesheet->hours);
                        $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
                        $dailyHours += $hours;
                    }
                }
            }
            
            $values[] = round($dailyHours ?: 0, 1);
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    // Helper methods for work patterns analysis
    private function getMostProductiveDay($timesheets)
    {
        if ($timesheets->isEmpty()) return 'N/A';
        
        $dayTotals = $timesheets->groupBy(function($item) {
            return Carbon::parse($item->date)->dayOfWeek;
        })->map(function($dayGroup) {
            return $dayGroup->sum('hours_worked');
        });
        
        $maxDay = $dayTotals->keys()->first();
        return Carbon::now()->dayOfWeek($maxDay)->format('l');
    }

    private function getAverageStartTime($timesheets)
    {
        $startTimes = $timesheets->whereNotNull('start_time')
            ->pluck('start_time')
            ->filter();
            
        if ($startTimes->isEmpty()) return 'N/A';
        
        // Convert times to minutes and calculate average
        $totalMinutes = $startTimes->sum(function($time) {
            [$hours, $minutes] = explode(':', $time);
            return ($hours * 60) + $minutes;
        });
        
        $avgMinutes = $totalMinutes / $startTimes->count();
        $avgHours = floor($avgMinutes / 60);
        $avgMins = $avgMinutes % 60;
        
        return sprintf('%02d:%02d', $avgHours, $avgMins);
    }

    private function getWorkConsistency($timesheets)
    {
        if ($timesheets->count() < 2) return 'N/A';
        
        $hoursWorked = $timesheets->pluck('hours_worked');
        $average = $hoursWorked->avg();
        $variance = $hoursWorked->sum(function($hours) use ($average) {
            return pow($hours - $average, 2);
        }) / $hoursWorked->count();
        
        $consistency = 100 - min(($variance / $average) * 100, 100);
        return round($consistency, 1) . '%';
    }
}