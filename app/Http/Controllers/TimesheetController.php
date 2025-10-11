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
            \Log::info('Timesheet storeOrUpdate called', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

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

            \Log::info('Prepared data for timesheet', $data);

            if ($request->filled('id')) {
                // Update existing entry
                $timesheet = Timesheet::where('id', $validated['id'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();
                    
                $timesheet->update($data);
                $message = 'Timesheet entry updated successfully';
                \Log::info('Updated existing timesheet entry', ['id' => $timesheet->id]);
            } else {
                // Check if entry already exists for this date
                $existing = Timesheet::where('user_id', $user->id)
                    ->where('date', $validated['date'])
                    ->first();
                    
                if ($existing) {
                    // Update existing entry
                    $existing->update($data);
                    $message = 'Existing timesheet entry updated successfully';
                    \Log::info('Updated existing timesheet entry for date', ['date' => $validated['date']]);
                } else {
                    // Create new entry
                    $data['user_id'] = $user->id;
                    $timesheet = Timesheet::create($data);
                    $message = 'New timesheet entry created successfully';
                    \Log::info('Created new timesheet entry', ['id' => $timesheet->id]);
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
            \Log::error('Timesheet save error: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString()
            ]);
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
        try {
            // Handle both HH:MM and HH:MM:SS formats
            $start = Carbon::createFromFormat('H:i:s', $clockIn) ?? Carbon::createFromFormat('H:i', $clockIn);
            $end = Carbon::createFromFormat('H:i:s', $clockOut) ?? Carbon::createFromFormat('H:i', $clockOut);
            
            if ($end->lessThan($start)) {
                $end->addDay(); // Handle overnight work
            }
        
        return $end->diffInMinutes($start) / 60;
        } catch (\Exception $e) {
            \Log::error('Duration calculation error: ' . $e->getMessage(), [
                'clock_in' => $clockIn,
                'clock_out' => $clockOut
            ]);
            return 0;
        }
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
            return 0.0; // Return 0 if no times provided
        }
        
        try {
            // Handle both HH:MM and HH:MM:SS formats
            $start = Carbon::createFromFormat('H:i:s', $startTime) ?? Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i:s', $endTime) ?? Carbon::createFromFormat('H:i', $endTime);
            
            if ($end->lessThan($start)) {
                $end->addDay(); // Handle overnight work
            }
            
            return round($end->diffInMinutes($start) / 60, 2);
        } catch (\Exception $e) {
            \Log::error('Hours calculation error: ' . $e->getMessage(), [
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            return 0.0; // Return 0 on error
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
        if ($request->filled('department')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department', $request->get('department'));
            });
        }
        if ($request->filled('task')) {
            $query->where('task', 'like', '%'.$request->get('task').'%');
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $perPage = $request->get('per_page', 10);
        $entries = $query->paginate($perPage)->withQueryString();
        return view('timesheet.admin', [
            'entries' => $entries,
        ]);
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        try {
            \Log::info('TimesheetController summary called', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'page' => $page,
                'per_page' => $perPage
            ]);

            $query = Timesheet::where('user_id', $user->id);
            
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
                \Log::info('Using provided date range', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
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
            

            // Get paginated results
            $paginatedRecords = $query->orderBy('date', 'desc')->paginate($perPage, ['*'], 'page', $page);
            $records = $paginatedRecords->items();
            
            \Log::info('Timesheet records found', [
                'count' => $paginatedRecords->total(),
                'current_page' => $paginatedRecords->currentPage(),
                'per_page' => $paginatedRecords->perPage(),
                'last_page' => $paginatedRecords->lastPage()
            ]);

            $formattedRecords = collect($records)->map(function($record) {
                // Calculate duration properly
                $duration = 0;
                if ($record->start_time && $record->end_time) {
                    $duration = $this->calculateHoursFromTimes($record->start_time, $record->end_time);
                } elseif ($record->hours_worked) {
                    $duration = $record->hours_worked;
                }
                
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

            // Calculate stats for all user records (not just current page)
            $stats = $this->calculateUserStats($user->id);

            return response()->json([
                'success' => true,
                'records' => $formattedRecords,
                'pagination' => [
                    'current_page' => $paginatedRecords->currentPage(),
                    'last_page' => $paginatedRecords->lastPage(),
                    'per_page' => $paginatedRecords->perPage(),
                    'total' => $paginatedRecords->total(),
                    'from' => $paginatedRecords->firstItem(),
                    'to' => $paginatedRecords->lastItem(),
                    'has_more_pages' => $paginatedRecords->hasMorePages()
                ],
                'stats' => $stats,
                'debug' => [
                    'total_records' => $paginatedRecords->total(),
                    'user_id' => $user->id,
                    'date_range' => [$startDate, $endDate]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Timesheet summary error: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load timesheet data: ' . $e->getMessage()
            ], 500);
        }
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

    private function calculateUserStats($userId)
    {
        $now = now();
        $today = $now->format('Y-m-d');
        $weekStart = $now->copy()->startOfWeek()->format('Y-m-d');
        $weekEnd = $now->copy()->endOfWeek()->format('Y-m-d');
        $monthStart = $now->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $now->copy()->endOfMonth()->format('Y-m-d');

        // Get all timesheets for the user
        $allTimesheets = Timesheet::where('user_id', $userId)->get();

        $todayHours = 0;
        $weekHours = 0;
        $monthHours = 0;
        $totalEntries = $allTimesheets->count();

        foreach ($allTimesheets as $timesheet) {
            $recordDate = $timesheet->date;
            if ($recordDate instanceof \Carbon\Carbon) {
                $recordDate = $recordDate->format('Y-m-d');
            }

            // Calculate duration for this record
            $duration = 0;
            if ($timesheet->start_time && $timesheet->end_time) {
                $duration = $this->calculateHoursFromTimes($timesheet->start_time, $timesheet->end_time);
            } elseif ($timesheet->hours_worked) {
                $duration = $timesheet->hours_worked;
            }

            // Add to today's hours
            if ($recordDate === $today) {
                $todayHours += $duration;
            }

            // Add to week's hours
            if ($recordDate >= $weekStart && $recordDate <= $weekEnd) {
                $weekHours += $duration;
            }

            // Add to month's hours
            if ($recordDate >= $monthStart && $recordDate <= $monthEnd) {
                $monthHours += $duration;
            }
        }

        return [
            'today_hours' => round($todayHours, 1),
            'week_hours' => round($weekHours, 1),
            'month_hours' => round($monthHours, 1),
            'total_entries' => $totalEntries
        ];
    }
}


