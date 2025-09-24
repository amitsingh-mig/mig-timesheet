<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\Timesheet;
use App\Models\WorkSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;

class UnifiedDashboardController extends Controller
{
    /**
     * Main dashboard with comprehensive data from all sources
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $queryUserId = $request->query('user_id');
        $forUserId = $queryUserId && $user->role && $user->role->name === 'admin' ? (int) $queryUserId : $user->id;
        
        $today = Carbon::today();
        
        // Get comprehensive data
        $data = $this->getComprehensiveData($forUserId);
        
        return response()->json([
            'success' => true,
            'user_id' => $forUserId,
            'date' => $today->toDateString(),
            ...$data
        ]);
    }

    /**
     * Get comprehensive data according to processing rules
     */
    private function getComprehensiveData($userId)
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $yearStart = $today->copy()->startOfYear();
        $yearEnd = $today->copy()->endOfYear();

        // Process attendance timeline (multiple sessions)
        $attendanceTimeline = $this->processAttendanceTimeline($userId, $today);
        
        // Process timesheet entries with task management
        $timesheetData = $this->processTimesheetEntries($userId, $today);
        
        // Get work summaries
        $workSummaries = $this->getWorkSummaries($userId, $today);
        
        // Calculate aggregated totals
        $totals = $this->calculateAggregatedTotals($userId, $today, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd);
        
        // Handle missing data
        $dataStatus = $this->handleMissingData($userId, $attendanceTimeline, $timesheetData);
        
        return [
            'attendance_timeline' => $attendanceTimeline,
            'timesheet_data' => $timesheetData,
            'work_summaries' => $workSummaries,
            'totals' => $totals,
            'data_status' => $dataStatus,
            'processing_summary' => $this->getProcessingSummary($attendanceTimeline, $timesheetData, $workSummaries)
        ];
    }

    /**
     * Process attendance timeline with multiple sessions per day
     */
    private function processAttendanceTimeline($userId, $date)
    {
        // Get today's sessions
        $todaySessions = AttendanceSession::forUser($userId)
            ->forDate($date)
            ->orderedBySession()
            ->get();
            
        // Auto clock-out missing sessions from previous days
        AttendanceSession::autoClockOutMissingSessions();
        
        // Process each session
        $processedSessions = $todaySessions->map(function($session, $index) {
            $sessionData = [
                'session_number' => $session->session_order,
                'clock_in' => $session->clock_in ? $session->clock_in->format('H:i:s') : null,
                'clock_out' => $session->clock_out ? $session->clock_out->format('H:i:s') : null,
                'duration' => $session->duration_formatted,
                'duration_minutes' => $session->duration_in_minutes,
                'status' => $session->status,
                'is_auto_clock_out' => $session->is_auto_clock_out,
                'notes' => $session->notes,
            ];
            
            // Add auto clock-out marker if needed
            if ($session->clock_in && !$session->clock_out) {
                $sessionData['pending_auto_clock_out'] = true;
                $sessionData['auto_clock_out_time'] = '23:59';
                $sessionData['status_message'] = 'Auto Clock-Out at 11:59 PM if not clocked out manually';
            } elseif ($session->is_auto_clock_out) {
                $sessionData['status_message'] = 'Auto Clock-Out (system added)';
            } else {
                $sessionData['status_message'] = $session->status_message;
            }
            
            return $sessionData;
        });
        
        // Calculate daily total
        $totalMinutes = $todaySessions->sum('duration_in_minutes');
        $totalFormatted = sprintf('%02d:%02d', intval($totalMinutes / 60), $totalMinutes % 60);
        
        return [
            'date' => $date->toDateString(),
            'sessions' => $processedSessions,
            'total_sessions' => $todaySessions->count(),
            'total_duration' => $totalFormatted,
            'total_minutes' => $totalMinutes,
            'has_active_session' => $todaySessions->where('status', 'active')->count() > 0,
            'active_session' => $todaySessions->where('status', 'active')->first()?->session_order,
            'message' => $todaySessions->count() > 0 ? 
                "Recorded {$todaySessions->count()} session(s) with total duration of {$totalFormatted}" : 
                "No attendance sessions recorded for today"
        ];
    }

    /**
     * Process timesheet entries with task management
     */
    private function processTimesheetEntries($userId, $date)
    {
        // Get today's timesheet entries
        $todayEntries = Timesheet::where('user_id', $userId)
            ->where('date', $date->toDateString())
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Merge duplicate tasks
        $mergedTasks = $this->mergeTasksByName($todayEntries);
        
        // Flag inconsistencies
        $flaggedTasks = $mergedTasks->map(function($task) {
            $task['inconsistency_flags'] = $this->flagTaskInconsistencies($task);
            return $task;
        });
        
        // Calculate totals
        $totalMinutes = $this->sumTimeInMinutes($todayEntries->pluck('hours')->all());
        $totalFormatted = sprintf('%02d:%02d', intval($totalMinutes / 60), $totalMinutes % 60);
        
        return [
            'date' => $date->toDateString(),
            'original_entries' => $todayEntries->count(),
            'merged_tasks' => $flaggedTasks,
            'total_tasks' => $flaggedTasks->count(),
            'total_duration' => $totalFormatted,
            'total_minutes' => $totalMinutes,
            'message' => $todayEntries->count() > 0 ? 
                "Recorded {$todayEntries->count()} task entries (merged into {$flaggedTasks->count()} unique tasks)" : 
                "No tasks recorded"
        ];
    }

    /**
     * Merge duplicate tasks by name on the same day
     */
    private function mergeTasksByName($entries)
    {
        $grouped = $entries->groupBy('task');
        
        return $grouped->map(function($taskEntries, $taskName) {
            $totalMinutes = $this->sumTimeInMinutes($taskEntries->pluck('hours')->all());
            $totalFormatted = sprintf('%02d:%02d', intval($totalMinutes / 60), $totalMinutes % 60);
            
            // Get work summaries for all related entries
            $workSummaries = collect();
            foreach ($taskEntries as $entry) {
                $summaries = WorkSummary::where('type', 'task')
                    ->where('related_id', $entry->id)
                    ->get();
                $workSummaries = $workSummaries->merge($summaries);
            }
            
            return [
                'task_name' => $taskName,
                'original_entries_count' => $taskEntries->count(),
                'merged_duration' => $totalFormatted,
                'merged_minutes' => $totalMinutes,
                'entries' => $taskEntries->map(function($entry) {
                    return [
                        'id' => $entry->id,
                        'hours' => $entry->hours,
                        'created_at' => $entry->created_at->format('H:i:s'),
                    ];
                }),
                'work_summaries' => $workSummaries,
                'is_merged' => $taskEntries->count() > 1,
            ];
        })->values();
    }

    /**
     * Flag task inconsistencies
     */
    private function flagTaskInconsistencies($task)
    {
        $flags = [];
        
        // Long duration flag
        if ($task['merged_minutes'] > 480) { // More than 8 hours
            $flags[] = [
                'type' => 'long_duration',
                'severity' => 'medium',
                'message' => 'Task duration exceeds 8 hours ({$task["merged_duration"]})',
            ];
        }
        
        // Duplicate entries flag
        if ($task['is_merged']) {
            $flags[] = [
                'type' => 'duplicate_entries',
                'severity' => 'low',
                'message' => "Task has {$task['original_entries_count']} separate entries (consider consolidating)",
            ];
        }
        
        return $flags;
    }

    /**
     * Get work summaries
     */
    private function getWorkSummaries($userId, $date)
    {
        $summaries = WorkSummary::forUser($userId)
            ->forDate($date)
            ->orderBy('type', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('type');
            
        return [
            'daily' => $summaries->get('daily', collect()),
            'session' => $summaries->get('session', collect()),
            'task' => $summaries->get('task', collect()),
            'total_count' => $summaries->flatten()->count(),
            'message' => $summaries->flatten()->count() > 0 ? 
                "Found {$summaries->flatten()->count()} work summaries" : 
                "No work summaries available"
        ];
    }

    /**
     * Calculate aggregated totals for today, week, month, year
     */
    private function calculateAggregatedTotals($userId, $today, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd)
    {
        // Attendance totals
        $attendanceTotals = [
            'today' => $this->formatMinutes(AttendanceSession::forUser($userId)->forDate($today)->sum('duration_in_minutes')),
            'this_week' => $this->formatMinutes(AttendanceSession::forUser($userId)->inDateRange($weekStart, $weekEnd)->sum('duration_in_minutes')),
            'this_month' => $this->formatMinutes(AttendanceSession::forUser($userId)->inDateRange($monthStart, $monthEnd)->sum('duration_in_minutes')),
            'this_year' => $this->formatMinutes(AttendanceSession::forUser($userId)->inDateRange($yearStart, $yearEnd)->sum('duration_in_minutes')),
        ];
        
        // Timesheet totals
        $timesheetTotals = [
            'today' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->where('date', $today->toDateString())->pluck('hours')->all()),
            'this_week' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])->pluck('hours')->all()),
            'this_month' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->pluck('hours')->all()),
            'this_year' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->whereBetween('date', [$yearStart->toDateString(), $yearEnd->toDateString()])->pluck('hours')->all()),
        ];
        
        return [
            'attendance' => $attendanceTotals,
            'timesheet' => $timesheetTotals,
            'period_labels' => [
                'today' => $today->format('Y-m-d'),
                'this_week' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'this_month' => $monthStart->format('F Y'),
                'this_year' => $yearStart->format('Y'),
            ]
        ];
    }

    /**
     * Handle missing data with appropriate messages
     */
    private function handleMissingData($userId, $attendanceTimeline, $timesheetData)
    {
        $hasAttendance = $attendanceTimeline['total_sessions'] > 0;
        $hasTimesheet = $timesheetData['total_tasks'] > 0;
        
        $status = [
            'has_attendance_data' => $hasAttendance,
            'has_timesheet_data' => $hasTimesheet,
            'attendance_message' => $hasAttendance ? null : 'No attendance data available.',
            'timesheet_message' => $hasTimesheet ? null : 'No tasks recorded.',
            'overall_status' => 'complete'
        ];
        
        // Determine overall status
        if (!$hasAttendance && !$hasTimesheet) {
            $status['overall_status'] = 'no_data';
            $status['overall_message'] = 'No attendance or timesheet data available for today.';
        } elseif (!$hasAttendance) {
            $status['overall_status'] = 'missing_attendance';
            $status['overall_message'] = 'Timesheet data available but no attendance records found.';
        } elseif (!$hasTimesheet) {
            $status['overall_status'] = 'missing_timesheet';
            $status['overall_message'] = 'Attendance data available but no task entries found.';
        } else {
            $status['overall_message'] = 'Both attendance and timesheet data available.';
        }
        
        return $status;
    }

    /**
     * Get processing summary
     */
    private function getProcessingSummary($attendanceTimeline, $timesheetData, $workSummaries)
    {
        return [
            'attendance_sessions_processed' => $attendanceTimeline['total_sessions'],
            'timesheet_entries_processed' => $timesheetData['original_entries'],
            'tasks_after_merging' => $timesheetData['total_tasks'],
            'work_summaries_found' => $workSummaries['total_count'],
            'auto_clock_out_applied' => $attendanceTimeline['sessions']->where('is_auto_clock_out', true)->count(),
            'duplicate_tasks_merged' => $timesheetData['original_entries'] - $timesheetData['total_tasks'],
        ];
    }

    /**
     * Get historical timeline for date range
     */
    public function timeline(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);
        
        $user = $request->user();
        $queryUserId = $request->input('user_id');
        $forUserId = $queryUserId && $user->role && $user->role->name === 'admin' ? (int) $queryUserId : $user->id;
        
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        
        $timeline = [];
        $cursor = $startDate->copy();
        
        while ($cursor->lte($endDate)) {
            $dailyData = $this->getDailyComprehensiveData($forUserId, $cursor);
            $timeline[$cursor->format('Y-m-d')] = $dailyData;
            $cursor->addDay();
        }
        
        return response()->json([
            'success' => true,
            'timeline' => $timeline,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'summary' => $this->calculateTimelineSummary($timeline)
        ]);
    }

    /**
     * Get daily comprehensive data for timeline
     */
    private function getDailyComprehensiveData($userId, $date)
    {
        $attendanceTimeline = $this->processAttendanceTimeline($userId, $date);
        $timesheetData = $this->processTimesheetEntries($userId, $date);
        $workSummaries = $this->getWorkSummaries($userId, $date);
        $dataStatus = $this->handleMissingData($userId, $attendanceTimeline, $timesheetData);
        
        return [
            'date' => $date->toDateString(),
            'day_name' => $date->format('l'),
            'attendance' => $attendanceTimeline,
            'timesheet' => $timesheetData,
            'work_summaries' => $workSummaries,
            'data_status' => $dataStatus,
        ];
    }

    /**
     * Calculate timeline summary statistics
     */
    private function calculateTimelineSummary($timeline)
    {
        $totalDays = count($timeline);
        $daysWithAttendance = 0;
        $daysWithTimesheet = 0;
        $totalAttendanceMinutes = 0;
        $totalTimesheetMinutes = 0;
        $totalSessions = 0;
        $totalTasks = 0;
        
        foreach ($timeline as $dailyData) {
            if ($dailyData['attendance']['total_sessions'] > 0) {
                $daysWithAttendance++;
                $totalAttendanceMinutes += $dailyData['attendance']['total_minutes'];
                $totalSessions += $dailyData['attendance']['total_sessions'];
            }
            
            if ($dailyData['timesheet']['total_tasks'] > 0) {
                $daysWithTimesheet++;
                $totalTimesheetMinutes += $dailyData['timesheet']['total_minutes'];
                $totalTasks += $dailyData['timesheet']['total_tasks'];
            }
        }
        
        return [
            'total_days' => $totalDays,
            'days_with_attendance' => $daysWithAttendance,
            'days_with_timesheet' => $daysWithTimesheet,
            'total_attendance_time' => $this->formatMinutes($totalAttendanceMinutes),
            'total_timesheet_time' => $this->formatMinutes($totalTimesheetMinutes),
            'total_sessions' => $totalSessions,
            'total_tasks' => $totalTasks,
            'average_daily_attendance' => $daysWithAttendance > 0 ? $this->formatMinutes(intval($totalAttendanceMinutes / $daysWithAttendance)) : '00:00',
            'average_daily_timesheet' => $daysWithTimesheet > 0 ? $this->formatMinutes(intval($totalTimesheetMinutes / $daysWithTimesheet)) : '00:00',
        ];
    }

    /**
     * Utility methods
     */
    private function parseHhMmToMinutes(string $hhmm): int
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));
        return ($h * 60) + $m;
    }

    private function sumTimeInMinutes(array $times): int
    {
        $total = 0;
        foreach ($times as $t) {
            $total += $this->parseHhMmToMinutes($t);
        }
        return $total;
    }

    private function formatMinutes(int $minutes): string
    {
        return sprintf('%02d:%02d', intval($minutes / 60), $minutes % 60);
    }

    private function sumTimeAsHhMm(array $times): string
    {
        return $this->formatMinutes($this->sumTimeInMinutes($times));
    }
}
