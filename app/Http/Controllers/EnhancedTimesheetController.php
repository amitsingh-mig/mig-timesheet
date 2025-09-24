<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\AttendanceSession;
use App\Models\WorkSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class EnhancedTimesheetController extends Controller
{
    /**
     * Enhanced timesheet index with task merging and inconsistency flagging
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $queryUserId = $request->query('user_id');
        $forUserId = $queryUserId && $user->role && $user->role->name === 'admin' ? (int) $queryUserId : $user->id;

        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        // Get timesheet entries with merging
        $entries = $this->getProcessedTimesheetEntries($forUserId, $monthStart, $monthEnd);
        $allEntries = $this->getProcessedTimesheetEntries($forUserId, null, null, 20);
        
        // Get attendance data for inconsistency detection
        $attendanceData = AttendanceSession::forUser($forUserId)
            ->inDateRange($monthStart, $monthEnd)
            ->get()
            ->groupBy(function($session) {
                return $session->date->format('Y-m-d');
            });

        // Calculate comprehensive totals
        $totals = $this->calculateComprehensiveTotals($forUserId);

        // Detect inconsistencies
        $inconsistencies = $this->detectInconsistencies($forUserId, $monthStart, $monthEnd, $entries, $attendanceData);

        return view('timesheet.enhanced.index', [
            'entries' => $allEntries,
            'monthEntries' => $entries,
            'totals' => $totals,
            'inconsistencies' => $inconsistencies,
            'currentMonth' => $monthStart->format('F Y'),
            'monthStart' => $monthStart,
            'attendance_data' => $attendanceData,
        ]);
    }

    /**
     * Store timesheet entry with enhanced validation and task merging option
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'task' => 'required|string|max:255',
            'hours' => ['required', 'regex:/^([01]?[0-9]|2[0-4]):[0-5][0-9]$/'],
            'date' => 'required|date',
            'merge_duplicates' => 'nullable|boolean',
            'work_summary' => 'nullable|string|max:2000',
        ]);

        $userId = $request->user()->id;
        $mergeDuplicates = $request->input('merge_duplicates', true);
        
        try {
            DB::beginTransaction();
            
            // Check if merge is requested and duplicate exists
            if ($mergeDuplicates) {
                $existingEntry = Timesheet::where('user_id', $userId)
                    ->where('date', $validator['date'])
                    ->where('task', $validator['task'])
                    ->first();
                    
                if ($existingEntry) {
                    // Merge hours
                    $existingMinutes = $this->parseHhMmToMinutes($existingEntry->hours);
                    $newMinutes = $this->parseHhMmToMinutes($validator['hours']);
                    $totalMinutes = $existingMinutes + $newMinutes;
                    
                    // Check daily limit
                    $dayTotal = $this->sumTimeInMinutes(Timesheet::where('user_id', $userId)->where('date', $validator['date'])->where('id', '!=', $existingEntry->id)->pluck('hours')->all());
                    if (($dayTotal + $totalMinutes) > 24 * 60) {
                        throw new \Exception('Total hours per day cannot exceed 24:00');
                    }
                    
                    $existingEntry->update([
                        'hours' => $this->minutesToHhMm($totalMinutes)
                    ]);
                    
                    // Add work summary if provided
                    if ($request->filled('work_summary')) {
                        WorkSummary::createTaskSummary(
                            $userId,
                            $validator['date'],
                            $request->input('work_summary'),
                            $existingEntry->id
                        );
                    }
                    
                    DB::commit();
                    return redirect()->back()->with('success', 'Timesheet entry merged with existing task.');
                }
            }
            
            // Create new entry
            $dayTotal = $this->sumTimeInMinutes(Timesheet::where('user_id', $userId)->where('date', $validator['date'])->pluck('hours')->all());
            $newMinutes = $this->parseHhMmToMinutes($validator['hours']);
            if (($dayTotal + $newMinutes) > 24 * 60) {
                throw new \Exception('Total hours per day cannot exceed 24:00');
            }

            $timesheetEntry = Timesheet::create([
                'user_id' => $userId,
                'task' => $validator['task'],
                'hours' => sprintf('%02d:%02d', intdiv($newMinutes, 60), $newMinutes % 60),
                'date' => $validator['date'],
            ]);
            
            // Add work summary if provided
            if ($request->filled('work_summary')) {
                WorkSummary::createTaskSummary(
                    $userId,
                    $validator['date'],
                    $request->input('work_summary'),
                    $timesheetEntry->id
                );
            }
            
            DB::commit();
            return redirect()->back()->with('success', 'Timesheet entry added.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get processed timesheet entries with merging and inconsistency flagging
     */
    private function getProcessedTimesheetEntries($userId, $startDate = null, $endDate = null, $limit = null)
    {
        $query = Timesheet::where('user_id', $userId)
            ->with(['user'])
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->orderBy('date', $limit ? 'desc' : 'asc');
            
        if ($limit) {
            $entries = $query->paginate($limit);
        } else {
            $entries = $query->get();
        }
        
        // Add work summaries and inconsistency flags
        $processedEntries = $entries->map(function($entry) {
            $entry->work_summaries = WorkSummary::where('type', 'task')
                ->where('related_id', $entry->id)
                ->get();
                
            // Check for potential inconsistencies
            $entry->inconsistency_flags = $this->checkEntryInconsistencies($entry);
            
            return $entry;
        });
        
        return $limit ? $processedEntries : $processedEntries;
    }

    /**
     * Calculate comprehensive totals across all data sources
     */
    private function calculateComprehensiveTotals($userId)
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $yearStart = $today->copy()->startOfYear();
        $yearEnd = $today->copy()->endOfYear();

        // Timesheet totals
        $timesheetTotals = [
            'daily' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->where('date', $today->toDateString())->pluck('hours')->all()),
            'weekly' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])->pluck('hours')->all()),
            'monthly' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->pluck('hours')->all()),
            'yearly' => $this->sumTimeAsHhMm(Timesheet::where('user_id', $userId)->whereBetween('date', [$yearStart->toDateString(), $yearEnd->toDateString()])->pluck('hours')->all()),
        ];

        // Attendance totals
        $attendanceTotals = [
            'daily' => $this->formatMinutes(AttendanceSession::forUser($userId)->forDate($today)->sum('duration_in_minutes')),
            'weekly' => $this->formatMinutes(AttendanceSession::forUser($userId)->inDateRange($weekStart, $weekEnd)->sum('duration_in_minutes')),
            'monthly' => $this->formatMinutes(AttendanceSession::forUser($userId)->inDateRange($monthStart, $monthEnd)->sum('duration_in_minutes')),
            'yearly' => $this->formatMinutes(AttendanceSession::forUser($userId)->inDateRange($yearStart, $yearEnd)->sum('duration_in_minutes')),
        ];

        return [
            'timesheet' => $timesheetTotals,
            'attendance' => $attendanceTotals,
            'data_status' => [
                'has_timesheet_data' => Timesheet::where('user_id', $userId)->exists(),
                'has_attendance_data' => AttendanceSession::forUser($userId)->exists(),
                'timesheet_message' => Timesheet::where('user_id', $userId)->exists() ? null : 'No tasks recorded.',
                'attendance_message' => AttendanceSession::forUser($userId)->exists() ? null : 'No attendance data available.',
            ]
        ];
    }

    /**
     * Detect inconsistencies between timesheet and attendance data
     */
    private function detectInconsistencies($userId, $startDate, $endDate, $timesheetEntries, $attendanceData)
    {
        $inconsistencies = [];
        
        // Group timesheet entries by date
        $timesheetByDate = $timesheetEntries->groupBy(function($entry) {
            return Carbon::parse($entry->date)->format('Y-m-d');
        });
        
        foreach ($timesheetByDate as $date => $dayEntries) {
            $timesheetMinutes = $this->sumTimeInMinutes($dayEntries->pluck('hours')->all());
            $attendanceMinutes = isset($attendanceData[$date]) ? 
                $attendanceData[$date]->sum('duration_in_minutes') : 0;
                
            $difference = abs($timesheetMinutes - $attendanceMinutes);
            
            // Flag if difference is more than 15 minutes
            if ($difference > 15) {
                $inconsistencies[] = [
                    'date' => $date,
                    'type' => 'time_mismatch',
                    'timesheet_hours' => $this->minutesToHhMm($timesheetMinutes),
                    'attendance_hours' => $this->minutesToHhMm($attendanceMinutes),
                    'difference' => $this->minutesToHhMm($difference),
                    'severity' => $difference > 60 ? 'high' : 'medium',
                    'message' => "Timesheet ({$this->minutesToHhMm($timesheetMinutes)}) vs Attendance ({$this->minutesToHhMm($attendanceMinutes)}) mismatch"
                ];
            }
            
            // Check for timesheet without attendance
            if ($timesheetMinutes > 0 && $attendanceMinutes == 0) {
                $inconsistencies[] = [
                    'date' => $date,
                    'type' => 'missing_attendance',
                    'timesheet_hours' => $this->minutesToHhMm($timesheetMinutes),
                    'attendance_hours' => '00:00',
                    'severity' => 'high',
                    'message' => "Tasks recorded ({$this->minutesToHhMm($timesheetMinutes)}) but no attendance data"
                ];
            }
        }
        
        // Check for attendance without timesheet
        foreach ($attendanceData as $date => $sessions) {
            if (!isset($timesheetByDate[$date])) {
                $attendanceMinutes = $sessions->sum('duration_in_minutes');
                if ($attendanceMinutes > 0) {
                    $inconsistencies[] = [
                        'date' => $date,
                        'type' => 'missing_timesheet',
                        'timesheet_hours' => '00:00',
                        'attendance_hours' => $this->minutesToHhMm($attendanceMinutes),
                        'severity' => 'medium',
                        'message' => "Attendance recorded ({$this->minutesToHhMm($attendanceMinutes)}) but no tasks logged"
                    ];
                }
            }
        }
        
        // Sort by date and severity
        usort($inconsistencies, function($a, $b) {
            if ($a['date'] == $b['date']) {
                $severityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
                return $severityOrder[$a['severity']] <=> $severityOrder[$b['severity']];
            }
            return $a['date'] <=> $b['date'];
        });
        
        return $inconsistencies;
    }

    /**
     * Check individual entry for inconsistencies
     */
    private function checkEntryInconsistencies($entry)
    {
        $flags = [];
        
        // Check for unusually long task duration
        $minutes = $this->parseHhMmToMinutes($entry->hours);
        if ($minutes > 480) { // More than 8 hours
            $flags[] = [
                'type' => 'long_duration',
                'severity' => 'medium',
                'message' => 'Task duration exceeds 8 hours'
            ];
        }
        
        // Check for duplicate task names on same date
        $duplicateCount = Timesheet::where('user_id', $entry->user_id)
            ->where('date', $entry->date)
            ->where('task', $entry->task)
            ->where('id', '!=', $entry->id)
            ->count();
            
        if ($duplicateCount > 0) {
            $flags[] = [
                'type' => 'duplicate_task',
                'severity' => 'low',
                'message' => 'Duplicate task on same date (consider merging)'
            ];
        }
        
        return $flags;
    }

    /**
     * Utility methods for time calculations
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

    private function minutesToHhMm(int $minutes): string
    {
        $minutes = max(0, $minutes);
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    private function sumTimeAsHhMm(array $times): string
    {
        return $this->minutesToHhMm($this->sumTimeInMinutes($times));
    }

    private function formatMinutes(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * API endpoint for getting inconsistencies
     */
    public function getInconsistencies(Request $request)
    {
        $user = $request->user();
        $queryUserId = $request->query('user_id');
        $forUserId = $queryUserId && $user->role && $user->role->name === 'admin' ? (int) $queryUserId : $user->id;
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        
        $entries = $this->getProcessedTimesheetEntries($forUserId, $startDate, $endDate);
        $attendanceData = AttendanceSession::forUser($forUserId)
            ->inDateRange($startDate, $endDate)
            ->get()
            ->groupBy(function($session) {
                return $session->date->format('Y-m-d');
            });
            
        $inconsistencies = $this->detectInconsistencies($forUserId, $startDate, $endDate, $entries, $attendanceData);
        
        return response()->json([
            'success' => true,
            'inconsistencies' => $inconsistencies,
            'summary' => [
                'total_count' => count($inconsistencies),
                'high_severity' => count(array_filter($inconsistencies, fn($i) => $i['severity'] === 'high')),
                'medium_severity' => count(array_filter($inconsistencies, fn($i) => $i['severity'] === 'medium')),
                'low_severity' => count(array_filter($inconsistencies, fn($i) => $i['severity'] === 'low')),
            ]
        ]);
    }

    /**
     * API endpoint for merging duplicate tasks
     */
    public function mergeDuplicateTasks(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'task' => 'required|string',
        ]);
        
        try {
            $userId = $request->user()->id;
            $date = $request->input('date');
            $task = $request->input('task');
            
            $duplicateEntries = Timesheet::where('user_id', $userId)
                ->where('date', $date)
                ->where('task', $task)
                ->get();
                
            if ($duplicateEntries->count() <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No duplicate tasks found to merge'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Calculate total hours
            $totalMinutes = $duplicateEntries->sum(function($entry) {
                return $this->parseHhMmToMinutes($entry->hours);
            });
            
            // Keep the first entry, update its hours
            $firstEntry = $duplicateEntries->first();
            $firstEntry->update([
                'hours' => $this->minutesToHhMm($totalMinutes)
            ]);
            
            // Merge work summaries
            $workSummaries = [];
            foreach ($duplicateEntries->skip(1) as $entry) {
                $summaries = WorkSummary::where('type', 'task')
                    ->where('related_id', $entry->id)
                    ->get();
                    
                foreach ($summaries as $summary) {
                    $summary->update(['related_id' => $firstEntry->id]);
                    $workSummaries[] = $summary->summary;
                }
                
                // Delete the duplicate entry
                $entry->delete();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Tasks merged successfully',
                'merged_entry' => $firstEntry->fresh(),
                'total_hours' => $this->minutesToHhMm($totalMinutes),
                'merged_count' => $duplicateEntries->count() - 1
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to merge tasks: ' . $e->getMessage()
            ], 500);
        }
    }
}
