<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;
use App\Models\WorkSummary;
use Illuminate\Support\Facades\Gate;

class AttendanceController extends Controller
{
    public function indexPage(Request $request)
    {
        return view('attendance.index');
    }

    /**
     * Get attendance data for the overview page
     */
    public function getData(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $status = $request->get('status');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $limit = $request->get('limit');
        $isAdmin = $user->role && $user->role->name === 'admin';

        try {
            // Get attendance records - for admin, get all users' records
            if ($isAdmin) {
                $query = Attendance::with('user')
                    ->whereBetween('date', [$startDate, $endDate]);
            } else {
                $query = Attendance::where('user_id', $user->id)
                    ->whereBetween('date', [$startDate, $endDate]);
            }

            if ($status) {
                $query->where('status', $status);
            }

            // If limit is specified (for recent activity), use limit instead of pagination
            if ($limit) {
                $records = $query->orderBy('date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
                $isLimitedQuery = true;
            } else {
                $records = $query->orderBy('date', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
                $isLimitedQuery = false;
            }

            // Calculate statistics
            if ($isAdmin) {
                $allRecords = Attendance::with('user')
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
            } else {
                $allRecords = Attendance::where('user_id', $user->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
            }

            $presentDays = $allRecords->where('clock_in', '!=', null)->count();
            $absentDays = max(0, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1 - $presentDays);
            $totalMinutes = $allRecords->sum('total_minutes');
            $totalHours = round($totalMinutes / 60, 1);
            $attendanceRate = $presentDays + $absentDays > 0 ? round(($presentDays / ($presentDays + $absentDays)) * 100) : 0;

            // Distribution for pie chart
            $distribution = [
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $allRecords->where('clock_in', '>', '09:00:00')->count(),
                'leave' => 0 // You can add leave logic here
            ];

            // Format records for display
            $formattedRecords = $records->map(function($record) use ($isAdmin) {
                $formatted = [
                    'id' => $record->id,
                    'date' => $record->date instanceof \Carbon\Carbon ? $record->date->format('Y-m-d') : $record->date,
                    'status' => $record->clock_in ? 'present' : 'absent',
                    'clock_in' => $record->clock_in ? (is_string($record->clock_in) ? $record->clock_in : $record->clock_in->format('H:i')) : null,
                    'clock_out' => $record->clock_out ? (is_string($record->clock_out) ? $record->clock_out : $record->clock_out->format('H:i')) : null,
                    'total_hours' => $record->total_hours ?? 0
                ];
                
                // Add user name for admin view
                if ($isAdmin && $record->user) {
                    $formatted['user_name'] = $record->user->name;
                    $formatted['user_email'] = $record->user->email;
                }
                
                return $formatted;
            });

            $response = [
                'success' => true,
                'attendance' => $formattedRecords, // Use 'attendance' for recent activity compatibility
                'records' => $formattedRecords,
                'statistics' => [
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'total_hours' => $totalHours,
                    'attendance_rate' => $attendanceRate
                ],
                'distribution' => $distribution
            ];
            
            // Only add pagination for paginated queries
            if (!$isLimitedQuery) {
                $response['pagination'] = [
                    'current_page' => $records->currentPage(),
                    'total_pages' => $records->lastPage(),
                    'total' => $records->total()
                ];
            }
            
            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showPage(Request $request, $id)
    {
        if (Gate::denies('report', $id)) {
            return redirect()->route('attendance.index')->with('error', 'Unauthorized');
        }
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $attendance = Attendance::where('user_id', $id)
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
            })
            ->orderBy('date', 'asc')
            ->paginate(30);

        $user = User::find($id);
        return view('attendance.show', [
            'attendance' => $attendance,
            'user' => $user,
            'start' => $start,
            'end' => $end,
        ]);
    }
    public function clock_in(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $today = Carbon::today();

            // Find the most recent attendance record for today
            $latestAttendance = Attendance::where('date', $today)
                ->where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // If no attendance record exists, create new one
            if (!$latestAttendance) {
                $attendance = Attendance::create([
                    'user_id' => $user_id,
                    'date' => $today,
                    'clock_in' => Carbon::now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully clocked in',
                    'status' => 'clocked_in',
                    'time' => $attendance->clock_in->format('H:i:s'),
                    'attendance' => $attendance
                ], 200);
            }

            // If the latest session is complete (both clock_in and clock_out exist),
            // allow starting a new session
            if ($latestAttendance->clock_in && $latestAttendance->clock_out) {
                $attendance = Attendance::create([
                    'user_id' => $user_id,
                    'date' => $today,
                    'clock_in' => Carbon::now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully clocked in for new session',
                    'status' => 'clocked_in',
                    'time' => $attendance->clock_in->format('H:i:s'),
                    'attendance' => $attendance
                ], 200);
            }

            // If already clocked in and not clocked out (active session), 
            // automatically clock out the previous session and start a new one
            if ($latestAttendance->clock_in && !$latestAttendance->clock_out) {
                // Clock out the previous session
                $latestAttendance->clock_out = Carbon::now();
                $latestAttendance->save();
                
                // Create new attendance record for new session
                $attendance = Attendance::create([
                    'user_id' => $user_id,
                    'date' => $today,
                    'clock_in' => Carbon::now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Previous session ended. New session started successfully',
                    'status' => 'clocked_in',
                    'time' => $attendance->clock_in->format('H:i:s'),
                    'attendance' => $attendance
                ], 200);
            }

            // If there's an attendance record without clock_in, update it
            $latestAttendance->clock_in = Carbon::now();
            $latestAttendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked in',
                'status' => 'clocked_in',
                'time' => $latestAttendance->clock_in->format('H:i:s'),
                'attendance' => $latestAttendance
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock in: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function clock_out(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $today = Carbon::today();

            // Find the most recent active attendance session for today (clocked in but not out)
            $attendance = Attendance::where('date', $today)
                ->where('user_id', $user_id)
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->orderBy('created_at', 'desc')
                ->first();

            // If no active attendance session exists
            if (!$attendance) {
                // Check if there are any completed sessions today
                $completedSessions = Attendance::where('date', $today)
                    ->where('user_id', $user_id)
                    ->whereNotNull('clock_in')
                    ->whereNotNull('clock_out')
                    ->count();
                
                if ($completedSessions > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'All work sessions for today are already completed. Please clock in to start a new session.',
                        'status' => 'no_active_session'
                    ], 400);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active clock-in session found for today. Please clock in first.',
                        'status' => 'no_clock_in'
                    ], 400);
                }
            }

            // Clock out
            $attendance->clock_out = Carbon::now();
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked out',
                'status' => 'clocked_out',
                'time' => $attendance->clock_out->format('H:i:s'),
                'total_hours' => $attendance->total_hours,
                'duration_minutes' => $attendance->total_minutes,
                'attendance' => $attendance
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock out: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function status(Request $request)
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'status' => 'unauthenticated',
                    'message' => 'Please log in to continue',
                    'redirect' => route('login')
                ], 401);
            }
            
            $user_id = $request->user()->id;
            $today = Carbon::today();

            // Get the most recent attendance record for today
            $attendance = Attendance::where('date', $today)
                ->where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Check if there's an active session (clocked in but not out)
            $activeSession = Attendance::where('date', $today)
                ->where('user_id', $user_id)
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->orderBy('created_at', 'desc')
                ->first();

            // If there's an active session
            if ($activeSession) {
                return response()->json([
                    'success' => true,
                    'status' => 'clocked_in',
                    'message' => 'Currently clocked in since ' . $activeSession->clock_in->format('H:i'),
                    'attendance' => $activeSession,
                    'total_hours' => $activeSession->total_hours,
                    'current_duration' => $activeSession->total_hours,
                    'is_clocked_in' => true,
                    'is_clocked_out' => false,
                    'can_clock_in' => false,
                    'can_clock_out' => true
                ]);
            }
            
            // If there's no attendance record at all
            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'status' => 'not_started',
                    'message' => 'Ready to clock in',
                    'can_clock_in' => true,
                    'can_clock_out' => false
                ]);
            }
            
            // If the most recent session is completed, user can start a new session
            if ($attendance->clock_in && $attendance->clock_out) {
                // Get total hours for all sessions today
                $totalHoursToday = Attendance::where('date', $today)
                    ->where('user_id', $user_id)
                    ->whereNotNull('clock_in')
                    ->whereNotNull('clock_out')
                    ->get()
                    ->sum('total_minutes');
                
                $hours = intval($totalHoursToday / 60);
                $mins = $totalHoursToday % 60;
                $totalHoursFormatted = sprintf('%02d:%02d', $hours, $mins);
                
                return response()->json([
                    'success' => true,
                    'status' => 'can_start_new',
                    'message' => 'Sessions completed today. Ready to start new session.',
                    'attendance' => $attendance,
                    'total_hours_today' => $totalHoursFormatted,
                    'sessions_completed' => Attendance::where('date', $today)->where('user_id', $user_id)->whereNotNull('clock_out')->count(),
                    'can_clock_in' => true,
                    'can_clock_out' => false
                ]);
            }
            
            // Default case
            return response()->json([
                'success' => true,
                'status' => 'not_started',
                'message' => 'Ready to clock in',
                'can_clock_in' => true,
                'can_clock_out' => false
            ]);

            return response()->json($status);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function report(Request $request, $id)

    {
        //authorize for the user itself and the admin
        if (Gate::denies('report', $id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        };

        //input validator
        $validator = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        //get attendance
        //by user id and date range 
        $attendance = Attendance::where('user_id', $id)->whereBetween('date', [
                $validator['start_date'],
                $validator['end_date']
            ])->orderBy('date', 'asc')->get();

        //return response
        return response()->json($attendance, 200);
    }

    public function all_report(Request $request)
    {
        //authorize only for the admin
        if (Gate::denies('report')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        };

        //input validator
        $validator = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $start_date = $validator['start_date'];
        $end_date = $validator['end_date'];
        //get all attendance


        //by date range 
        $users = User::with(['attendances' => function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->orderBy('date', 'asc');
        }])->get();

        //return response
        return response()->json($users, 200);
    }
    
    /**
     * Get attendance data for calendar display
     */
    public function calendar(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('n'));
            
            // Get first and last day of the month
            $firstDay = Carbon::create($year, $month, 1)->startOfDay();
            $lastDay = $firstDay->copy()->endOfMonth()->endOfDay();
            
            // Get all attendance records for the month
            $attendances = Attendance::where('user_id', $user_id)
                ->whereBetween('date', [$firstDay->toDateString(), $lastDay->toDateString()])
                ->whereNotNull('clock_in')
                ->get()
                ->groupBy(function($item) {
                    return $item->date->format('Y-m-d');
                })
                ->map(function($dayAttendances) {
                    $totalMinutes = $dayAttendances->sum('total_minutes');
                    $hours = intval($totalMinutes / 60);
                    $mins = $totalMinutes % 60;
                    
                    return [
                        'date' => $dayAttendances->first()->date->format('Y-m-d'),
                        'total_hours' => sprintf('%02d:%02d', $hours, $mins),
                        'total_minutes' => $totalMinutes,
                        'sessions_count' => $dayAttendances->count(),
                        'complete_sessions' => $dayAttendances->where('clock_out', '!=', null)->count(),
                        'has_active_session' => $dayAttendances->where('clock_out', null)->count() > 0
                    ];
                })
                ->values();
            
            // Generate holiday information for the month
            $holidays = $this->getHolidaysForMonth($year, $month);
            
            // Generate day type information for all days in the month
            $dayTypes = $this->getDayTypesForMonth($year, $month, $attendances, $holidays);
            
            return response()->json([
                'success' => true,
                'data' => $attendances,
                'day_types' => $dayTypes,
                'holidays' => $holidays,
                'month' => $month,
                'year' => $year
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calendar data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get holidays for a specific month
     */
    private function getHolidaysForMonth($year, $month)
    {
        $holidays = [];
        $firstDay = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        
        // Define festival days (you can move this to config or database)
        $festivalDays = $this->getFestivalDays($year, $month);
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateString = $date->format('Y-m-d');
            
            // Check if it's Sunday
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                $holidays[] = [
                    'date' => $dateString,
                    'type' => 'sunday',
                    'name' => 'Sunday'
                ];
                continue;
            }
            
            // Check if it's 1st, 3rd, or 5th Saturday
            if ($date->dayOfWeek === Carbon::SATURDAY) {
                $saturdayNumber = $this->getSaturdayNumber($date);
                if (in_array($saturdayNumber, [1, 3, 5])) {
                    $holidays[] = [
                        'date' => $dateString,
                        'type' => 'saturday',
                        'name' => $this->getOrdinal($saturdayNumber) . ' Saturday'
                    ];
                    continue;
                }
            }
            
            // Check if it's a festival day
            if (in_array($dateString, $festivalDays)) {
                $holidays[] = [
                    'date' => $dateString,
                    'type' => 'festival',
                    'name' => 'Festival Day'
                ];
            }
        }
        
        return $holidays;
    }
    
    /**
     * Get festival days for a specific month (you can customize this)
     */
    private function getFestivalDays($year, $month)
    {
        // Define your festival days here
        // You can move this to a config file or database
        $festivals = [
            // January
            1 => ['2025-01-01', '2025-01-26'], // New Year, Republic Day
            // March  
            3 => ['2025-03-14'], // Holi
            // April
            4 => ['2025-04-14'], // Good Friday (example)
            // August
            8 => ['2025-08-15'], // Independence Day
            // September
            9 => ['2025-09-02'], // Ganesh Chaturthi (example)
            // October
            10 => ['2025-10-02', '2025-10-24'], // Gandhi Jayanti, Diwali (example)
            // November
            11 => ['2025-11-12'], // Diwali extended (example)
            // December
            12 => ['2025-12-25'], // Christmas
        ];
        
        return $festivals[$year][$month] ?? $festivals[$month] ?? [];
    }
    
    /**
     * Get which Saturday of the month this date is (1st, 2nd, 3rd, etc.)
     */
    private function getSaturdayNumber($date)
    {
        $firstDay = $date->copy()->startOfMonth();
        $saturdayCount = 0;
        
        for ($d = $firstDay->copy(); $d->month === $date->month; $d->addDay()) {
            if ($d->dayOfWeek === Carbon::SATURDAY) {
                $saturdayCount++;
                if ($d->day === $date->day) {
                    return $saturdayCount;
                }
            }
        }
        
        return $saturdayCount;
    }
    
    /**
     * Get ordinal suffix for numbers (1st, 2nd, 3rd, etc.)
     */
    private function getOrdinal($number)
    {
        $suffixes = ['', '1st', '2nd', '3rd', '4th', '5th'];
        return $suffixes[$number] ?? $number . 'th';
    }
    
    /**
     * Get day types for all days in the month
     */
    private function getDayTypesForMonth($year, $month, $attendances, $holidays)
    {
        $dayTypes = [];
        $firstDay = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $today = Carbon::today();
        
        // Create lookup arrays for quick access
        $attendanceDates = $attendances->pluck('date')->toArray();
        $holidayDates = collect($holidays)->pluck('date')->toArray();
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateString = $date->format('Y-m-d');
            
            $dayType = [
                'date' => $dateString,
                'day' => $day,
                'is_today' => $date->isSameDay($today),
                'is_holiday' => in_array($dateString, $holidayDates),
                'is_work_day' => in_array($dateString, $attendanceDates),
                'holiday_info' => null
            ];
            
            // Add holiday info if it's a holiday
            if ($dayType['is_holiday']) {
                $holiday = collect($holidays)->firstWhere('date', $dateString);
                $dayType['holiday_info'] = $holiday;
            }
            
            $dayTypes[] = $dayType;
        }
        
        return $dayTypes;
    }
}
