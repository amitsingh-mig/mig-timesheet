<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository
{
    /**
     * Get attendance records with optimized queries
     */
    public function getAttendanceData(array $filters = []): array
    {
        $userId = $filters['user_id'] ?? null;
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();
        $status = $filters['status'] ?? null;
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 10;
        $limit = $filters['limit'] ?? null;
        $isAdmin = $filters['is_admin'] ?? false;

        // Base query with eager loading
        if ($isAdmin) {
            $query = Attendance::with(['user:id,name,email'])
                ->whereBetween('date', [$startDate, $endDate]);
        } else {
            $query = Attendance::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate]);
        }

        // Apply status filter
        if ($status) {
            if ($status === 'present') {
                $query->whereNotNull('clock_in');
            } elseif ($status === 'absent') {
                $query->whereNull('clock_in');
            }
        }

        // Get records
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

        // Calculate statistics efficiently
        $stats = $this->calculateAttendanceStatistics($startDate, $endDate, $userId, $isAdmin);

        // Format records
        $formattedRecords = $this->formatAttendanceRecords($records, $isAdmin);

        $response = [
            'success' => true,
            'attendance' => $formattedRecords,
            'records' => $formattedRecords,
            'statistics' => $stats['statistics'],
            'distribution' => $stats['distribution']
        ];

        // Add pagination for paginated queries
        if (!$isLimitedQuery) {
            $response['pagination'] = [
                'current_page' => $records->currentPage(),
                'total_pages' => $records->lastPage(),
                'total' => $records->total()
            ];
        }

        return $response;
    }

    /**
     * Calculate attendance statistics efficiently
     */
    private function calculateAttendanceStatistics(string $startDate, string $endDate, $userId, bool $isAdmin): array
    {
        // Use single query for statistics
        if ($isAdmin) {
            $allRecords = Attendance::selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN clock_in IS NOT NULL THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN TIME(clock_in) > "09:00:00" THEN 1 ELSE 0 END) as late_count,
                SUM(total_minutes) as total_minutes
            ')
            ->whereBetween('date', [$startDate, $endDate])
            ->first();
        } else {
            $allRecords = Attendance::selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN clock_in IS NOT NULL THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN TIME(clock_in) > "09:00:00" THEN 1 ELSE 0 END) as late_count,
                SUM(total_minutes) as total_minutes
            ')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->first();
        }

        $presentDays = $allRecords->present_count ?? 0;
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $absentDays = max(0, $totalDays - $presentDays);
        $totalMinutes = $allRecords->total_minutes ?? 0;
        $totalHours = round($totalMinutes / 60, 1);
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;
        $lateCount = $allRecords->late_count ?? 0;

        return [
            'statistics' => [
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'total_hours' => $totalHours,
                'attendance_rate' => $attendanceRate
            ],
            'distribution' => [
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateCount,
                'leave' => 0 // Can be extended
            ]
        ];
    }

    /**
     * Format attendance records for display
     */
    private function formatAttendanceRecords($records, bool $isAdmin): Collection
    {
        $collection = $records instanceof LengthAwarePaginator ? $records->getCollection() : $records;
        
        return $collection->map(function($record) use ($isAdmin) {
            $formatted = [
                'id' => $record->id,
                'date' => $record->date instanceof Carbon ? $record->date->format('Y-m-d') : $record->date,
                'status' => $record->clock_in ? 'present' : 'absent',
                'clock_in' => $record->formatted_clock_in,
                'clock_out' => $record->formatted_clock_out,
                'total_hours' => $record->total_hours ?? '00:00'
            ];
            
            // Add user info for admin view
            if ($isAdmin && $record->user) {
                $formatted['user_name'] = $record->user->name;
                $formatted['user_email'] = $record->user->email;
            }
            
            return $formatted;
        });
    }

    /**
     * Get user attendance report with efficient queries
     */
    public function getUserAttendanceReport($userId, array $filters = []): Collection
    {
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        return Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get all users attendance report (admin only)
     */
    public function getAllUsersAttendanceReport(array $filters = []): Collection
    {
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        return User::with(['attendances' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc')
                ->select(['id', 'user_id', 'date', 'clock_in', 'clock_out', 'total_minutes']);
        }])
        ->whereHas('attendances', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        })
        ->select(['id', 'name', 'email'])
        ->get();
    }

    /**
     * Get calendar data with optimized queries
     */
    public function getCalendarData($userId, int $year, int $month): array
    {
        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth()->endOfDay();
        
        // Get all attendance records for the month with single query
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->whereNotNull('clock_in')
            ->selectRaw('
                DATE(date) as date,
                SUM(total_minutes) as total_minutes,
                COUNT(*) as sessions_count,
                SUM(CASE WHEN clock_out IS NOT NULL THEN 1 ELSE 0 END) as complete_sessions,
                SUM(CASE WHEN clock_out IS NULL THEN 1 ELSE 0 END) as active_sessions
            ')
            ->groupBy('date')
            ->get()
            ->map(function($item) {
                $hours = intval($item->total_minutes / 60);
                $mins = $item->total_minutes % 60;
                
                return [
                    'date' => $item->date,
                    'total_hours' => sprintf('%02d:%02d', $hours, $mins),
                    'total_minutes' => $item->total_minutes,
                    'sessions_count' => $item->sessions_count,
                    'complete_sessions' => $item->complete_sessions,
                    'has_active_session' => $item->active_sessions > 0
                ];
            });

        return [
            'data' => $attendances,
            'month' => $month,
            'year' => $year
        ];
    }

    /**
     * Get active attendance session
     */
    public function getActiveSession($userId): ?Attendance
    {
        return Attendance::where('date', Carbon::today())
            ->where('user_id', $userId)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get today's completed sessions
     */
    public function getTodayCompletedSessions($userId): Collection
    {
        return Attendance::where('date', Carbon::today())
            ->where('user_id', $userId)
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}