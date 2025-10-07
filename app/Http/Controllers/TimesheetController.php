<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        return view('timesheet.index');
    }

    /**
     * Get timesheet summary data for calendar view
     */
    public function getSummaryData(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        try {
            \Log::info('TimesheetController getSummaryData called', [
                'user_id' => $user->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $query = Timesheet::where('user_id', $user->id);
            
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // Default to current week if no dates provided
                $now = now();
                $weekStart = $now->copy()->startOfWeek();
                $weekEnd = $now->copy()->endOfWeek();
                $query->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')]);
                \Log::info('Using default week range', [
                    'week_start' => $weekStart->format('Y-m-d'),
                    'week_end' => $weekEnd->format('Y-m-d')
                ]);
            }

            $records = $query->orderBy('date', 'desc')->get();
            \Log::info('Timesheet records found', ['count' => $records->count()]);

            $formattedRecords = $records->map(function($record) {
                $duration = $record->hours_worked ?: $this->calculateHoursFromTimes($record->start_time, $record->end_time);
                
                // Handle date formatting safely
                $dateFormatted = $record->date;
                if ($dateFormatted instanceof \Carbon\Carbon) {
                    $dateFormatted = $dateFormatted->format('Y-m-d');
                } else if (is_string($dateFormatted) && strpos($dateFormatted, 'T') !== false) {
                    $dateFormatted = \Carbon\Carbon::parse($dateFormatted)->format('Y-m-d');
                }
                
                return [
                    'id' => $record->id,
                    'date' => $dateFormatted,
                    'clock_in' => $record->start_time ?: '09:00',
                    'clock_out' => $record->end_time ?: '17:00', 
                    'duration' => $duration,
                    'task_description' => $record->description ?: $record->task ?: 'No description',
                    'tasks' => $record->description ?: $record->task ?: 'No description'
                ];
            });

            \Log::info('Formatted records prepared', ['records' => $formattedRecords->toArray()]);

            return response()->json([
                'success' => true,
                'records' => $formattedRecords,
                'debug' => [
                    'total_records' => $records->count(),
                    'user_id' => $user->id,
                    'date_range' => [$startDate, $endDate]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Timesheet getSummaryData error: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load timesheet data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store or update timesheet entry
     */
    public function storeOrUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'clock_in' => 'required|date_format:H:i',
                'clock_out' => 'required|date_format:H:i|after:clock_in',
                'task_description' => 'required|string|max:1000',
                'id' => 'nullable|integer|exists:timesheets,id'
            ]);

            $user = Auth::user();
            $duration = $this->calculateDuration($validated['clock_in'], $validated['clock_out']);
            $hoursFormatted = sprintf('%02d:%02d', intval($duration), round(($duration - intval($duration)) * 60));

            $data = [
                'date' => $validated['date'],
                'start_time' => $validated['clock_in'],
                'end_time' => $validated['clock_out'],
                'hours_worked' => $duration,
                'description' => $validated['task_description'],
                'task' => $validated['task_description'], // For backward compatibility
                'hours' => $hoursFormatted
            ];

            if ($request->filled('id')) {
                // Update existing entry
                $timesheet = Timesheet::where('id', $validated['id'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();
                    
                $timesheet->update($data);
                $message = 'Timesheet entry updated successfully';
            } else {
                // Check if entry already exists for this date
                $existing = Timesheet::where('user_id', $user->id)
                    ->where('date', $validated['date'])
                    ->first();
                    
                if ($existing) {
                    // Update existing entry
                    $existing->update($data);
                    $message = 'Existing timesheet entry updated successfully';
                } else {
                    // Create new entry
                    $data['user_id'] = $user->id;
                    Timesheet::create($data);
                    $message = 'New timesheet entry created successfully';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Timesheet validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Timesheet save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save timesheet entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete timesheet entry
     */
    public function destroy($id)
    {
        try {
            $timesheet = Timesheet::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
                
            $timesheet->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet entry deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete timesheet entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export timesheet data
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $user = Auth::user();
        
        $query = Timesheet::where('user_id', $user->id);
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        
        $records = $query->orderBy('date', 'desc')->get();
        
        $filename = "timesheet_" . $user->name . "_" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        
        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Clock In', 'Clock Out', 'Duration', 'Description']);
            
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->date,
                    $record->start_time,
                    $record->end_time,
                    $record->hours_worked . ' hours',
                    $record->description ?? $record->task
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate duration between two times
     */
    private function calculateDuration($clockIn, $clockOut)
    {
        $start = Carbon::createFromFormat('H:i', $clockIn);
        $end = Carbon::createFromFormat('H:i', $clockOut);
        
        return $end->diffInMinutes($start) / 60;
    }

    /**
     * Calculate hours from start and end time
     */
    private function calculateHours($startTime, $endTime)
    {
        if (!$startTime || !$endTime) {
            return 0;
        }
        
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        
        return $end->diffInMinutes($start) / 60;
    }
    
    /**
     * Calculate hours from times with better error handling
     */
    private function calculateHoursFromTimes($startTime, $endTime)
    {
        if (!$startTime || !$endTime) {
            return 8.0; // Default 8 hours
        }
        
        try {
            $start = Carbon::createFromFormat('H:i:s', $startTime) ?? Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i:s', $endTime) ?? Carbon::createFromFormat('H:i', $endTime);
            
            if ($end->lessThan($start)) {
                $end->addDay(); // Handle overnight work
            }
            
            return round($end->diffInMinutes($start) / 60, 2);
        } catch (\Exception $e) {
            return 8.0; // Default fallback
        }
    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'task' => 'required|string|max:255',
            'hours' => ['required','regex:/^([01]?[0-9]|2[0-4]):[0-5][0-9]$/'],
            'date' => 'required|date',
        ]);

        // constrain max 24:00 total per day for this user
        $userId = $request->user()->id;
        $dayTotal = $this->sumTimeInMinutes(Timesheet::where('user_id', $userId)->where('date', $validator['date'])->pluck('hours')->all());
        $newMinutes = $this->parseHhMmToMinutes($validator['hours']);
        if (($dayTotal + $newMinutes) > 24 * 60) {
            return redirect()->back()->with('error', 'Total hours per day cannot exceed 24:00');
        }

        Timesheet::create([
            'user_id' => $userId,
            'task' => $validator['task'],
            'hours' => sprintf('%02d:%02d', intdiv($newMinutes,60), $newMinutes%60),
            'date' => $validator['date'],
        ]);

        return redirect()->back()->with('success', 'Timesheet entry added.');
    }

    public function adminIndex(Request $request)
    {
        abort_unless(Gate::allows('admin'), 403);

        $query = Timesheet::with('user')->orderBy('date','desc');
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('task')) {
            $query->where('task', 'like', '%'.$request->get('task').'%');
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $entries = $query->paginate(25)->withQueryString();
        return view('timesheet.admin', [
            'entries' => $entries,
        ]);
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $queryUserId = $request->query('user_id');
        $forUserId = $queryUserId && $user->role && $user->role->name === 'admin' ? (int) $queryUserId : $user->id;

        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $yearStart = $today->copy()->startOfYear();
        $yearEnd = $today->copy()->endOfYear();

        // Get timesheet entries for different periods
        $monthEntries = Timesheet::where('user_id', $forUserId)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('date', 'asc')
            ->get(['date','hours', 'task']);

        $weekEntries = Timesheet::where('user_id', $forUserId)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get(['hours']);
            
        $todayEntries = Timesheet::where('user_id', $forUserId)
            ->where('date', $today->toDateString())
            ->get(['hours', 'task']);
            
        $yearEntries = Timesheet::where('user_id', $forUserId)
            ->whereBetween('date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->get(['hours']);

        // Check if this is a request for last 7 days (day range)
        $range = $request->query('range', 'month');
        
        $labels = [];
        $data = [];
        
        if ($range === 'day') {
            // Build data for last 7 days
            $sevenDaysAgo = $today->copy()->subDays(6); // 6 days ago + today = 7 days
            $cursor = $sevenDaysAgo->copy();
            
            // Get entries for last 7 days
            $weekEntries = Timesheet::where('user_id', $forUserId)
                ->whereBetween('date', [$sevenDaysAgo->toDateString(), $today->toDateString()])
                ->get(['date', 'hours']);
                
            while ($cursor->lte($today)) {
                $labels[] = $cursor->format('Y-m-d');
                $dayEntries = $weekEntries->where('date', $cursor->toDateString());
                $minutes = $this->sumTimeInMinutes($dayEntries->pluck('hours')->all());
                $data[] = round($minutes/60, 2);
                $cursor->addDay();
            }
        } elseif ($range === 'week') {
            // Build data for weeks in current month
            $monthStart = $today->copy()->startOfMonth();
            $monthEnd = $today->copy()->endOfMonth();
            $weekEntries = Timesheet::where('user_id', $forUserId)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get(['date', 'hours']);
                
            // Group by week
            $weekNum = 1;
            $cursor = $monthStart->copy()->startOfWeek();
            while ($cursor->lte($monthEnd)) {
                $weekStart = $cursor->copy();
                $weekEnd = $cursor->copy()->endOfWeek();
                if ($weekEnd->greaterThan($monthEnd)) {
                    $weekEnd = $monthEnd->copy();
                }
                
                $labels[] = "W{$weekNum}";
                $weeklyEntries = $weekEntries->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
                $minutes = $this->sumTimeInMinutes($weeklyEntries->pluck('hours')->all());
                $data[] = round($minutes/60, 2);
                
                $cursor->addWeek();
                $weekNum++;
                
                if ($weekNum > 5) break; // Max 5 weeks in a month
            }
        } elseif ($range === 'year') {
            // Build data for months in current year
            $yearStart = $today->copy()->startOfYear();
            $yearEnd = $today->copy()->endOfYear();
            $yearEntries = Timesheet::where('user_id', $forUserId)
                ->whereBetween('date', [$yearStart->toDateString(), $yearEnd->toDateString()])
                ->get(['date', 'hours']);
            
            for ($month = 1; $month <= 12; $month++) {
                $monthStart = Carbon::create($today->year, $month, 1);
                $monthEnd = $monthStart->copy()->endOfMonth();
                
                $labels[] = $monthStart->format('M');
                $monthlyEntries = $yearEntries->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                $minutes = $this->sumTimeInMinutes($monthlyEntries->pluck('hours')->all());
                $data[] = round($minutes/60, 2);
            }
        } else {
            // Build arrays for chart (by day for current month)
            $cursor = $monthStart->copy();
            
            while ($cursor->lte($monthEnd)) {
                $labels[] = $cursor->format('Y-m-d');
                $dayEntries = $monthEntries->where('date', $cursor->toDateString());
                $minutes = $this->sumTimeInMinutes($dayEntries->pluck('hours')->all());
                $data[] = round($minutes/60, 2);
                $cursor->addDay();
            }
        }

        // Calculate totals
        $todayMinutes = $this->sumTimeInMinutes($todayEntries->pluck('hours')->all());
        $weekMinutes = $this->sumTimeInMinutes($weekEntries->pluck('hours')->all());
        $monthMinutes = $this->sumTimeInMinutes($monthEntries->pluck('hours')->all());
        $yearMinutes = $this->sumTimeInMinutes($yearEntries->pluck('hours')->all());

        // Check for missing data and provide appropriate messages
        $hasTimesheetData = $monthEntries->count() > 0;
        $hasAttendanceData = $monthEntries->count() > 0; // Using timesheet as proxy for attendance
        $todayTasksCount = $todayEntries->count();
        
        // Get attendance data (using timesheet entries as proxy)
        $attendanceDays = $monthEntries->map(function($entry) {
            return $entry->date;
        })->unique()->values();

        // Calculate appropriate total based on range
        $rangeTotal = 0;
        if ($range === 'day') {
            // For day view, total should be sum of last 7 days
            $sevenDaysAgo = $today->copy()->subDays(6);
            $dayRangeEntries = Timesheet::where('user_id', $forUserId)
                ->whereBetween('date', [$sevenDaysAgo->toDateString(), $today->toDateString()])
                ->get(['hours']);
            $rangeTotal = $this->sumTimeInMinutes($dayRangeEntries->pluck('hours')->all());
        } elseif ($range === 'week') {
            // For week view, total should be current month total
            $rangeTotal = $monthMinutes;
        } elseif ($range === 'year') {
            // For year view, total should be current year total
            $rangeTotal = $yearMinutes;
        } else {
            // For month view, total should be current month total
            $rangeTotal = $monthMinutes;
        }

        // Build response with proper error handling
        $response = [
            'success' => true,
            'labels' => $labels,
            'data' => $data,
            'range_total_minutes' => $rangeTotal, // Add range-specific total
            'totals' => [
                'daily' => $this->minutesToHhMm($todayMinutes),
                'weekly' => $this->minutesToHhMm($weekMinutes),
                'monthly' => $this->minutesToHhMm($monthMinutes),
                'yearly' => $this->minutesToHhMm($yearMinutes),
            ],
            'today' => [
                'done_hours' => round($todayMinutes/60, 2),
                'target_hours' => 8,
                'tasks_count' => $todayTasksCount,
                'progress_percent' => min(100, round(($todayMinutes/60 / 8) * 100, 1))
            ],
            'attendance' => $attendanceDays,
            'data_status' => [
                'has_timesheet_data' => $hasTimesheetData,
                'has_attendance_data' => $hasAttendanceData,
                'timesheet_message' => $hasTimesheetData ? null : 'No tasks recorded for this period.',
                'attendance_message' => $hasAttendanceData ? null : 'No attendance data available.',
            ],
            'summary' => [
                'total_work_days' => $attendanceDays->count(),
                'avg_hours_per_day' => $attendanceDays->count() > 0 ? round($monthMinutes / 60 / $attendanceDays->count(), 1) : 0,
                'most_productive_day' => $this->getMostProductiveDay($monthEntries),
                'tasks_completed_today' => $todayTasksCount,
            ],
        ];

        return response()->json($response);
    }

    private function parseHhMmToMinutes(string $hhmm): int
    {
        [$h,$m] = array_map('intval', explode(':', $hhmm));
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
        return sprintf('%02d:%02d', intdiv($minutes,60), $minutes%60);
    }

    private function sumTimeAsHhMm(array $times): string
    {
        return $this->minutesToHhMm($this->sumTimeInMinutes($times));
    }

    private function getMostProductiveDay($entries)
    {
        if ($entries->isEmpty()) {
            return null;
        }

        $dayTotals = $entries->groupBy('date')->map(function($dayEntries) {
            return [
                'date' => $dayEntries->first()->date,
                'total_minutes' => $this->sumTimeInMinutes($dayEntries->pluck('hours')->all()),
                'tasks_count' => $dayEntries->count()
            ];
        });

        $mostProductive = $dayTotals->sortByDesc('total_minutes')->first();
        
        if (!$mostProductive || $mostProductive['total_minutes'] == 0) {
            return null;
        }

        return [
            'date' => $mostProductive['date'],
            'hours' => $this->minutesToHhMm($mostProductive['total_minutes']),
            'tasks' => $mostProductive['tasks_count']
        ];
    }
    
    private function calculateCalendarStats($userId, $monthStart, $monthEnd)
    {
        $today = Carbon::today();
        
        // Get all timesheet entries for the month
        $monthEntries = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();
            
        // Get today's entries
        $todayEntries = Timesheet::where('user_id', $userId)
            ->where('date', $today->toDateString())
            ->get();
            
        // Calculate work days (days with tasks)
        $workDays = $monthEntries->groupBy('date')->count();
        
        // Calculate total tasks
        $totalTasks = $monthEntries->count();
        
        // Calculate total hours
        $totalMinutes = $this->sumTimeInMinutes($monthEntries->pluck('hours')->all());
        $totalHours = round($totalMinutes / 60, 1);
        
        // Calculate average hours per day (only work days)
        $avgHoursPerDay = $workDays > 0 ? round($totalHours / $workDays, 1) : 0;
        
        // Count weekend days in the month
        $weekendDays = 0;
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            if ($cursor->isWeekend()) {
                $weekendDays++;
            }
            $cursor->addDay();
        }
        
        // Today's hours
        $todayMinutes = $this->sumTimeInMinutes($todayEntries->pluck('hours')->all());
        $todayHours = round($todayMinutes / 60, 1);
        
        return [
            'workDays' => $workDays,
            'totalDaysInMonth' => $monthStart->daysInMonth,
            'totalTasks' => $totalTasks,
            'avgHoursPerDay' => $avgHoursPerDay,
            'monthTotal' => $totalHours,
            'weekendDays' => $weekendDays,
            'todayHours' => $todayHours,
            'todayTasks' => $todayEntries->count(),
        ];
    }
    
    public function loadMore(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 2); // Default to page 2 since page 1 is already loaded
        $perPage = 5;
        $skip = ($page - 1) * $perPage;
        
        $entries = Timesheet::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->skip($skip)
            ->take($perPage)
            ->get();
            
        // Check if there are more entries
        $totalEntries = Timesheet::where('user_id', $user->id)->count();
        $hasMore = ($skip + $perPage) < $totalEntries;
        
        return response()->json([
            'entries' => $entries,
            'hasMore' => $hasMore,
            'currentPage' => $page,
            'totalEntries' => $totalEntries
        ]);
    }
    
    private function prepareCalendarData($userId, $monthStart, $monthEnd)
    {
        $today = Carbon::today();
        
        // Get tasks grouped by day with total hours
        $tasksByDay = Timesheet::selectRaw('DATE(date) as day, SUM(TIME_TO_SEC(hours))/3600 as total_hours, COUNT(*) as task_count')
            ->where('user_id', $userId)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('day')
            ->get()
            ->keyBy('day');
        
        $calendarData = [];
        
        // Calculate empty cells before month starts
        $firstDayOfWeek = $monthStart->dayOfWeek; // 0 = Sunday, 6 = Saturday
        
        // Add empty cells for days before month starts
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $calendarData[] = [
                'isEmpty' => true,
                'date' => null,
                'day' => null,
                'hours' => 0,
                'taskCount' => 0,
                'isToday' => false,
                'isWeekend' => false,
                'hasWork' => false,
            ];
        }
        
        // Add all days of the month
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            $dayKey = $cursor->toDateString();
            $dayData = $tasksByDay->get($dayKey);
            
            $hours = $dayData ? round($dayData->total_hours, 2) : 0;
            $taskCount = $dayData ? $dayData->task_count : 0;
            
            $calendarData[] = [
                'isEmpty' => false,
                'date' => $cursor->copy(),
                'day' => $cursor->day,
                'hours' => $hours,
                'taskCount' => $taskCount,
                'isToday' => $cursor->isSameDay($today),
                'isWeekend' => $cursor->isWeekend(),
                'hasWork' => $hours > 0,
                'formattedHours' => $this->formatDecimalHours($hours),
            ];
            
            $cursor->addDay();
        }
        
        // Fill remaining cells to complete the grid (make it divisible by 7)
        $totalCells = count($calendarData);
        $remainingCells = (7 - ($totalCells % 7)) % 7;
        
        for ($i = 0; $i < $remainingCells; $i++) {
            $calendarData[] = [
                'isEmpty' => true,
                'date' => null,
                'day' => null,
                'hours' => 0,
                'taskCount' => 0,
                'isToday' => false,
                'isWeekend' => false,
                'hasWork' => false,
            ];
        }
        
        return $calendarData;
    }
    
    private function formatDecimalHours($hours)
    {
        if ($hours == 0) return '';
        
        $totalMinutes = round($hours * 60);
        $h = intval($totalMinutes / 60);
        $m = $totalMinutes % 60;
        
        if ($m == 0) {
            return $h . 'h';
        }
        
        return sprintf('%d:%02d', $h, $m);
    }
    
    public function dayTasks(Request $request)
    {
        $user = $request->user();
        $date = $request->get('date');
        
        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }
        
        $tasks = Timesheet::where('user_id', $user->id)
            ->where('date', $date)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'tasks' => $tasks,
            'date' => $date,
            'total_tasks' => $tasks->count(),
            'total_hours' => $tasks->sum(function($task) {
                [$hours, $minutes] = explode(':', $task->hours);
                return (int)$hours + ((int)$minutes / 60);
            })
        ]);
    }
}


