<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Get attendance data with caching
     */
    public function getAttendanceData(array $filters = []): array
    {
        $cacheKey = 'attendance_data_' . md5(serialize($filters));
        $cacheDuration = 300; // 5 minutes

        // Don't cache for admins or real-time requests
        if ($filters['is_admin'] ?? false || ($filters['limit'] ?? null)) {
            return $this->attendanceRepository->getAttendanceData($filters);
        }

        return Cache::remember($cacheKey, $cacheDuration, function () use ($filters) {
            return $this->attendanceRepository->getAttendanceData($filters);
        });
    }

    /**
     * Clock in with business logic validation
     */
    public function clockIn(User $user): array
    {
        try {
            DB::beginTransaction();

            $today = Carbon::today();
            
            // Check for existing active session
            $activeSession = $this->attendanceRepository->getActiveSession($user->id);
            
            if ($activeSession) {
                return [
                    'success' => false,
                    'message' => 'Already clocked in at ' . $activeSession->formatted_clock_in,
                    'status' => 'already_clocked_in',
                    'attendance' => $activeSession
                ];
            }

            // Check for maximum daily sessions (business rule)
            $todaySessionsCount = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->count();
                
            if ($todaySessionsCount >= 5) { // Maximum 5 sessions per day
                return [
                    'success' => false,
                    'message' => 'Maximum daily clock-in sessions reached (5). Please contact your supervisor.',
                    'status' => 'max_sessions_reached'
                ];
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => Carbon::now(),
            ]);

            DB::commit();

            // Clear cache
            $this->clearUserAttendanceCache($user->id);

            return [
                'success' => true,
                'message' => 'Successfully clocked in',
                'status' => 'clocked_in',
                'time' => $attendance->formatted_clock_in,
                'attendance' => $attendance
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to clock in: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Clock out with business logic validation
     */
    public function clockOut(User $user): array
    {
        try {
            DB::beginTransaction();

            $activeSession = $this->attendanceRepository->getActiveSession($user->id);
            
            if (!$activeSession) {
                $completedSessions = $this->attendanceRepository->getTodayCompletedSessions($user->id);
                
                $message = $completedSessions->count() > 0
                    ? 'All work sessions for today are completed. Please clock in to start a new session.'
                    : 'No active clock-in session found. Please clock in first.';
                
                return [
                    'success' => false,
                    'message' => $message,
                    'status' => 'no_active_session',
                    'completed_sessions' => $completedSessions->count()
                ];
            }

            // Validate minimum work duration (business rule)
            $workDurationMinutes = $activeSession->total_minutes;
            if ($workDurationMinutes < 15) { // Minimum 15 minutes
                return [
                    'success' => false,
                    'message' => 'Minimum work duration is 15 minutes. Current duration: ' . $workDurationMinutes . ' minutes.',
                    'status' => 'minimum_duration_not_met'
                ];
            }

            // Clock out
            $activeSession->update([
                'clock_out' => Carbon::now()
            ]);

            $activeSession->refresh(); // Reload to get updated total_hours

            DB::commit();

            // Clear cache
            $this->clearUserAttendanceCache($user->id);

            return [
                'success' => true,
                'message' => 'Successfully clocked out',
                'status' => 'clocked_out',
                'time' => $activeSession->formatted_clock_out,
                'total_hours' => $activeSession->total_hours,
                'duration_minutes' => $activeSession->total_minutes,
                'attendance' => $activeSession
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to clock out: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Get attendance status with enhanced information
     */
    public function getAttendanceStatus(User $user): array
    {
        try {
            $today = Carbon::today();
            
            // Check for active session
            $activeSession = $this->attendanceRepository->getActiveSession($user->id);
            
            if ($activeSession) {
                return [
                    'success' => true,
                    'status' => 'clocked_in',
                    'message' => 'Currently clocked in since ' . $activeSession->formatted_clock_in,
                    'attendance' => $activeSession,
                    'current_duration' => $activeSession->current_duration,
                    'is_clocked_in' => true,
                    'is_clocked_out' => false,
                    'can_clock_in' => false,
                    'can_clock_out' => true,
                    'session_start' => $activeSession->clock_in->toISOString()
                ];
            }
            
            // Get completed sessions for today
            $completedSessions = $this->attendanceRepository->getTodayCompletedSessions($user->id);
            
            if ($completedSessions->count() > 0) {
                $totalMinutes = $completedSessions->sum('total_minutes');
                $hours = intval($totalMinutes / 60);
                $mins = $totalMinutes % 60;
                $totalHoursFormatted = sprintf('%02d:%02d', $hours, $mins);
                
                return [
                    'success' => true,
                    'status' => 'can_start_new',
                    'message' => 'Sessions completed today. Ready to start new session.',
                    'total_hours_today' => $totalHoursFormatted,
                    'sessions_completed' => $completedSessions->count(),
                    'last_session' => $completedSessions->first(),
                    'can_clock_in' => true,
                    'can_clock_out' => false
                ];
            }
            
            // No attendance records for today
            return [
                'success' => true,
                'status' => 'not_started',
                'message' => 'Ready to clock in',
                'can_clock_in' => true,
                'can_clock_out' => false,
                'sessions_completed' => 0,
                'total_hours_today' => '00:00'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Get calendar data for employee
     */
    public function getCalendarData(User $user, int $year, int $month): array
    {
        try {
            $cacheKey = "attendance_calendar_{$user->id}_{$year}_{$month}";
            $cacheDuration = 3600; // 1 hour
            
            return Cache::remember($cacheKey, $cacheDuration, function () use ($user, $year, $month) {
                $data = $this->attendanceRepository->getCalendarData($user->id, $year, $month);
                
                // Add business logic enhancements
                $data['holidays'] = $this->getHolidaysForMonth($year, $month);
                $data['day_types'] = $this->getDayTypesForMonth($year, $month, $data['data'], $data['holidays']);
                $data['monthly_stats'] = $this->calculateMonthlyStats($data['data']);
                
                return [
                    'success' => true,
                    'data' => $data['data'],
                    'day_types' => $data['day_types'],
                    'holidays' => $data['holidays'],
                    'monthly_stats' => $data['monthly_stats'],
                    'month' => $month,
                    'year' => $year
                ];
            });

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch calendar data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate attendance report
     */
    public function generateReport(array $filters): array
    {
        try {
            $userId = $filters['user_id'] ?? null;
            $isAdmin = $filters['is_admin'] ?? false;
            
            if ($isAdmin && !$userId) {
                // All users report
                $data = $this->attendanceRepository->getAllUsersAttendanceReport($filters);
            } else {
                // Single user report
                $data = $this->attendanceRepository->getUserAttendanceReport($userId, $filters);
            }
            
            return [
                'success' => true,
                'data' => $data,
                'summary' => $this->generateReportSummary($data, $filters),
                'generated_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get holidays for a specific month (business logic)
     */
    private function getHolidaysForMonth(int $year, int $month): array
    {
        // This could be moved to a configuration file or database
        $holidays = [];
        $firstDay = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateString = $date->format('Y-m-d');
            
            // Weekend detection
            if ($date->isWeekend()) {
                $holidays[] = [
                    'date' => $dateString,
                    'type' => $date->isSunday() ? 'sunday' : 'saturday',
                    'name' => $date->format('l')
                ];
            }
            
            // Add company-specific holidays here
            // This could be enhanced to fetch from database
        }
        
        return $holidays;
    }

    /**
     * Calculate monthly statistics
     */
    private function calculateMonthlyStats($attendanceData): array
    {
        $totalMinutes = 0;
        $workingDays = 0;
        $sessionsCount = 0;
        
        foreach ($attendanceData as $day) {
            $totalMinutes += $day['total_minutes'];
            $workingDays++;
            $sessionsCount += $day['sessions_count'];
        }
        
        $totalHours = $totalMinutes / 60;
        $averageHoursPerDay = $workingDays > 0 ? $totalHours / $workingDays : 0;
        
        return [
            'total_working_days' => $workingDays,
            'total_hours' => round($totalHours, 2),
            'total_sessions' => $sessionsCount,
            'average_hours_per_day' => round($averageHoursPerDay, 2),
            'average_sessions_per_day' => $workingDays > 0 ? round($sessionsCount / $workingDays, 1) : 0
        ];
    }

    /**
     * Generate day types for calendar
     */
    private function getDayTypesForMonth(int $year, int $month, $attendances, $holidays): array
    {
        $dayTypes = [];
        $firstDay = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $today = Carbon::today();
        
        $attendanceDates = collect($attendances)->pluck('date')->toArray();
        $holidayDates = collect($holidays)->pluck('date')->toArray();
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateString = $date->format('Y-m-d');
            
            $dayTypes[] = [
                'date' => $dateString,
                'day' => $day,
                'is_today' => $date->isSameDay($today),
                'is_holiday' => in_array($dateString, $holidayDates),
                'is_work_day' => in_array($dateString, $attendanceDates),
                'is_weekend' => $date->isWeekend(),
                'day_name' => $date->format('D')
            ];
        }
        
        return $dayTypes;
    }

    /**
     * Generate report summary
     */
    private function generateReportSummary($data, array $filters): array
    {
        // Implementation depends on data structure
        // This is a placeholder for summary logic
        return [
            'total_records' => is_countable($data) ? count($data) : 0,
            'date_range' => [
                'start' => $filters['start_date'] ?? null,
                'end' => $filters['end_date'] ?? null
            ],
            'generated_for' => $filters['user_id'] ? 'single_user' : 'all_users'
        ];
    }

    /**
     * Clear user attendance cache
     */
    private function clearUserAttendanceCache($userId): void
    {
        $keys = [
            "attendance_calendar_{$userId}_*",
            'attendance_data_*',
            "attendance_status_{$userId}"
        ];
        
        foreach ($keys as $pattern) {
            // In a real implementation, you'd use Cache::tags() or implement proper cache invalidation
            // For now, this is a placeholder
        }
    }

    /**
     * Validate attendance business rules
     */
    public function validateAttendanceRules(User $user, array $data): array
    {
        $errors = [];
        
        // Check working hours policy
        if (isset($data['clock_in']) && isset($data['clock_out'])) {
            $clockIn = Carbon::parse($data['clock_in']);
            $clockOut = Carbon::parse($data['clock_out']);
            $duration = $clockOut->diffInHours($clockIn);
            
            if ($duration > 12) {
                $errors[] = 'Work duration exceeds 12 hours. Overtime approval may be required.';
            }
            
            if ($duration < 0.25) {
                $errors[] = 'Minimum work duration is 15 minutes.';
            }
        }
        
        // Check weekend policy
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date']);
            if ($date->isWeekend() && (!$user->role || $user->role->name !== 'admin')) {
                $errors[] = 'Weekend work requires manager approval.';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}