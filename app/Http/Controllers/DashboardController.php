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
     * Display the dashboard view
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Get dashboard summary data
     */
    public function getData(Request $request)
    {
        $userId = Auth::id();
        
        try {
            $summary = $this->getSummaryData($userId);
            
            return response()->json([
                'success' => true,
                'today' => $summary['today'],
                'weekly' => $summary['weekly'], 
                'monthly' => $summary['monthly'],
                'tasks' => $summary['tasks']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get work time data for charts
     */
    public function getWorktimeData(Request $request)
    {
        $period = $request->get('period', 'week');
        $userId = Auth::id();
        
        try {
            $data = $this->getChartData($userId, $period);
            $summary = $this->getSummaryData($userId);
            
            return response()->json([
                'success' => true,
                'labels' => $data['labels'],
                'data' => $data['values'],
                'summary' => $summary
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load work time data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data based on period
     */
    private function getChartData($userId, $period)
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

    /**
     * Get daily data (24 hours)
     */
    private function getDailyData($userId, $date)
    {
        $labels = [];
        $values = [];
        
        // Create 24 hour labels
        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            $values[] = 0;
        }
        
        // Get timesheet entries for today
        $timesheets = Timesheet::where('user_id', $userId)
            ->whereDate('date', $date->toDateString())
            ->get();
        
        foreach ($timesheets as $timesheet) {
            $hours = 0;
            
            if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                // Use the calculated hours_worked field
                $hours = $timesheet->hours_worked;
            } elseif ($timesheet->start_time && $timesheet->end_time) {
                // Calculate hours from start and end times
                $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                $hours = $end->diffInMinutes($start) / 60;
            } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                // Fallback to legacy hours format (HH:MM)
                $hoursParts = explode(':', $timesheet->hours);
                $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
            }
            
            if ($hours > 0) {
                if ($timesheet->start_time) {
                    $startHour = (int)substr($timesheet->start_time, 0, 2);
                    $values[$startHour] += $hours;
                } else {
                    $values[9] += $hours; // Default to 9 AM if no specific time
                }
            }
        }
        
        return [
            'labels' => $labels,
            'values' => array_map(function($val) { return round($val, 1); }, $values)
        ];
    }

    /**
     * Get weekly data (7 days)
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
            $timesheets = Timesheet::where('user_id', $userId)
                ->whereDate('date', $currentDay->toDateString())
                ->get();
                
            $dailyHours = 0;
            foreach ($timesheets as $timesheet) {
                $hours = 0;
                
                if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                    // Use the calculated hours_worked field
                    $hours = $timesheet->hours_worked;
                } elseif ($timesheet->start_time && $timesheet->end_time) {
                    // Calculate hours from start and end times
                    $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                    $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                    $hours = $end->diffInMinutes($start) / 60;
                } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                    // Fallback to legacy hours format (HH:MM)
                    $hoursParts = explode(':', $timesheet->hours);
                    $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
                }
                
                $dailyHours += $hours;
            }
            
            $values[] = round($dailyHours ?: 0, 1);
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get monthly data (4 weeks)
     */
    private function getMonthlyData($userId, $date)
    {
        $labels = [];
        $values = [];
        
        $startOfMonth = $date->copy()->startOfMonth();
        $weeksInMonth = ceil($startOfMonth->daysInMonth / 7);
        
        for ($week = 1; $week <= $weeksInMonth; $week++) {
            $weekStart = $startOfMonth->copy()->addWeeks($week - 1);
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
            
            if ($weekEnd->greaterThan($startOfMonth->copy()->endOfMonth())) {
                $weekEnd = $startOfMonth->copy()->endOfMonth()->endOfDay();
            }
            
            $labels[] = 'Week ' . $week;
            
            // Get total hours for this week from timesheets
            $timesheets = Timesheet::where('user_id', $userId)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get();
                
            $weeklyHours = 0;
            foreach ($timesheets as $timesheet) {
                $hours = 0;
                
                if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                    // Use the calculated hours_worked field
                    $hours = $timesheet->hours_worked;
                } elseif ($timesheet->start_time && $timesheet->end_time) {
                    // Calculate hours from start and end times
                    $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                    $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                    $hours = $end->diffInMinutes($start) / 60;
                } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                    // Fallback to legacy hours format (HH:MM)
                    $hoursParts = explode(':', $timesheet->hours);
                    $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
                }
                
                $weeklyHours += $hours;
            }
            
            $values[] = round($weeklyHours ?: 0, 1);
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get yearly data (12 months)
     */
    private function getYearlyData($userId, $date)
    {
        $labels = [];
        $values = [];
        
        $startOfYear = $date->copy()->startOfYear();
        
        for ($month = 1; $month <= 12; $month++) {
            $currentMonth = $startOfYear->copy()->addMonths($month - 1);
            $labels[] = $currentMonth->format('M');
            
            // Get total hours for this month from timesheets
            $timesheets = Timesheet::where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $date->year)
                ->get();
                
            $monthlyHours = 0;
            foreach ($timesheets as $timesheet) {
                $hours = 0;
                
                if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                    // Use the calculated hours_worked field
                    $hours = $timesheet->hours_worked;
                } elseif ($timesheet->start_time && $timesheet->end_time) {
                    // Calculate hours from start and end times
                    $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                    $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                    $hours = $end->diffInMinutes($start) / 60;
                } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                    // Fallback to legacy hours format (HH:MM)
                    $hoursParts = explode(':', $timesheet->hours);
                    $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
                }
                
                $monthlyHours += $hours;
            }
            
            $values[] = round($monthlyHours ?: 0, 1);
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get summary data for cards
     */
    private function getSummaryData($userId)
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        // Today's hours from timesheets
        $timesheets = Timesheet::where('user_id', $userId)
            ->whereDate('date', $today->toDateString())
            ->get();
            
        $todayHours = 0;
        foreach ($timesheets as $timesheet) {
            $hours = 0;
            
            if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                // Use the calculated hours_worked field
                $hours = $timesheet->hours_worked;
            } elseif ($timesheet->start_time && $timesheet->end_time) {
                // Calculate hours from start and end times
                $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                $hours = $end->diffInMinutes($start) / 60;
            } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                // Fallback to legacy hours format (HH:MM)
                $hoursParts = explode(':', $timesheet->hours);
                $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
            }
            
            $todayHours += $hours;
        }
        
        // Weekly hours from timesheets
        $timesheets = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();
            
        $weeklyHours = 0;
        foreach ($timesheets as $timesheet) {
            $hours = 0;
            
            if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                // Use the calculated hours_worked field
                $hours = $timesheet->hours_worked;
            } elseif ($timesheet->start_time && $timesheet->end_time) {
                // Calculate hours from start and end times
                $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                $hours = $end->diffInMinutes($start) / 60;
            } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                // Fallback to legacy hours format (HH:MM)
                $hoursParts = explode(':', $timesheet->hours);
                $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
            }
            
            $weeklyHours += $hours;
        }
        
        // Monthly hours from timesheets
        $timesheets = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();
            
        $monthlyHours = 0;
        foreach ($timesheets as $timesheet) {
            $hours = 0;
            
            if ($timesheet->hours_worked && $timesheet->hours_worked > 0) {
                // Use the calculated hours_worked field
                $hours = $timesheet->hours_worked;
            } elseif ($timesheet->start_time && $timesheet->end_time) {
                // Calculate hours from start and end times
                $start = Carbon::createFromFormat('H:i:s', $timesheet->start_time);
                $end = Carbon::createFromFormat('H:i:s', $timesheet->end_time);
                $hours = $end->diffInMinutes($start) / 60;
            } elseif ($timesheet->hours && $timesheet->hours !== '00:00:00') {
                // Fallback to legacy hours format (HH:MM)
                $hoursParts = explode(':', $timesheet->hours);
                $hours = (int)$hoursParts[0] + ((int)($hoursParts[1] ?? 0) / 60);
            }
            
            $monthlyHours += $hours;
        }
        
        // Tasks done (from timesheet entries)
        $tasksDone = Timesheet::where('user_id', $userId)
            ->whereDate('date', $today)
            ->count();
        
        return [
            'today' => round($todayHours ?: 0, 1),
            'weekly' => round($weeklyHours ?: 0, 1),
            'monthly' => round($monthlyHours ?: 0, 1),
            'tasks' => $tasksDone ?: 0,
            // Legacy format for backward compatibility
            'today_hours' => round($todayHours ?: 0, 1),
            'weekly_hours' => round($weeklyHours ?: 0, 1),
            'monthly_hours' => round($monthlyHours ?: 0, 1),
            'tasks_done' => $tasksDone ?: 0
        ];
    }
}
