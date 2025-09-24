<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\WorkSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AttendanceSessionController extends Controller
{
    /**
     * Get attendance sessions with timeline for a user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        
        // Check if user is admin or regular user
        if ($user->role && $user->role->name === 'admin') {
            // Admin can see all users with their sessions
            $query = AttendanceSession::with(['user', 'workSummaries'])
                ->when($start && $end, function ($query) use ($start, $end) {
                    $query->inDateRange($start, $end);
                })
                ->orderedBySession();
                
            $sessions = $query->paginate(50);
            
            return view('attendance.sessions.index', [
                'sessions' => $sessions,
                'start' => $start,
                'end' => $end,
                'is_admin' => true,
            ]);
        } else {
            // Regular users see only their own sessions
            $sessions = AttendanceSession::forUser($user->id)
                ->with('workSummaries')
                ->when($start && $end, function ($query) use ($start, $end) {
                    $query->inDateRange($start, $end);
                })
                ->orderedBySession()
                ->paginate(30);
                
            return view('attendance.sessions.user', [
                'sessions' => $sessions,
                'user' => $user,
                'start' => $start,
                'end' => $end,
                'is_admin' => false,
            ]);
        }
    }

    /**
     * Start a new attendance session (Clock In)
     */
    public function clockIn(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $today = Carbon::today();
            
            // Check if there's already an active session
            $activeSession = AttendanceSession::forUser($user_id)
                ->forDate($today)
                ->active()
                ->first();
                
            if ($activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active session. Please clock out first.',
                    'status' => 'session_active',
                    'active_session' => $activeSession
                ], 400);
            }

            // Auto clock-out any previous day sessions
            AttendanceSession::autoClockOutMissingSessions();

            // Get next session order for today
            $sessionOrder = AttendanceSession::getNextSessionOrder($user_id, $today);

            // Create new session
            $session = AttendanceSession::create([
                'user_id' => $user_id,
                'date' => $today,
                'clock_in' => Carbon::now(),
                'session_order' => $sessionOrder,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked in (Session #' . $sessionOrder . ')',
                'status' => 'clocked_in',
                'session' => $session,
                'time' => $session->clock_in->format('H:i:s'),
                'session_number' => $sessionOrder
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock in: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * End current attendance session (Clock Out)
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            $user_id = $request->user()->id;
            $today = Carbon::today();
            
            // Find active session for today
            $activeSession = AttendanceSession::forUser($user_id)
                ->forDate($today)
                ->active()
                ->first();
                
            if (!$activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active session found. Please clock in first.',
                    'status' => 'no_active_session'
                ], 400);
            }

            // Clock out
            $activeSession->update([
                'clock_out' => Carbon::now(),
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked out',
                'status' => 'clocked_out',
                'session' => $activeSession->fresh(),
                'time' => $activeSession->clock_out->format('H:i:s'),
                'duration' => $activeSession->duration_formatted,
                'session_number' => $activeSession->session_order
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock out: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Get current attendance status
     */
    public function status(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $today = Carbon::today();
            
            // Get all today's sessions
            $todaySessions = AttendanceSession::forUser($user_id)
                ->forDate($today)
                ->orderedBySession()
                ->get();
                
            // Find active session
            $activeSession = $todaySessions->where('status', 'active')->first();
            
            // Calculate totals for today
            $totalMinutes = $todaySessions->sum('duration_in_minutes');
            $totalFormatted = sprintf('%02d:%02d', intval($totalMinutes / 60), $totalMinutes % 60);
            
            $status = [
                'success' => true,
                'today_sessions' => $todaySessions,
                'active_session' => $activeSession,
                'total_sessions' => $todaySessions->count(),
                'total_duration' => $totalFormatted,
                'total_minutes' => $totalMinutes,
                'can_clock_in' => !$activeSession,
                'can_clock_out' => (bool) $activeSession,
            ];

            if ($activeSession) {
                $status['status'] = 'active';
                $status['message'] = 'Session #' . $activeSession->session_order . ' active since ' . $activeSession->clock_in->format('H:i');
                $status['current_session_duration'] = $activeSession->duration_formatted;
            } elseif ($todaySessions->count() > 0) {
                $status['status'] = 'completed_sessions';
                $status['message'] = 'Completed ' . $todaySessions->count() . ' session(s) today (Total: ' . $totalFormatted . ')';
            } else {
                $status['status'] = 'not_started';
                $status['message'] = 'Ready to start first session';
            }

            return response()->json($status);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Get attendance timeline for a specific date or date range
     */
    public function timeline(Request $request, $userId = null)
    {
        $user = $request->user();
        $targetUserId = $userId ?: $user->id;
        
        // Authorization check
        if ($targetUserId != $user->id && (!$user->role || $user->role->name !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Get sessions with timeline
        $sessions = AttendanceSession::forUser($targetUserId)
            ->inDateRange($startDate, $endDate)
            ->with('workSummaries')
            ->orderedBySession()
            ->get();
            
        // Group by date
        $timeline = $sessions->groupBy(function($session) {
            return $session->date->format('Y-m-d');
        });
        
        // Calculate daily totals
        $dailyTotals = [];
        $overallTotal = 0;
        
        foreach ($timeline as $date => $daySessions) {
            $dailyMinutes = $daySessions->sum('duration_in_minutes');
            $dailyTotals[$date] = [
                'sessions_count' => $daySessions->count(),
                'total_minutes' => $dailyMinutes,
                'total_formatted' => sprintf('%02d:%02d', intval($dailyMinutes / 60), $dailyMinutes % 60),
                'has_incomplete_sessions' => $daySessions->where('status', 'active')->count() > 0,
                'sessions' => $daySessions->map(function($session) {
                    return [
                        'id' => $session->id,
                        'session_order' => $session->session_order,
                        'clock_in' => $session->clock_in,
                        'clock_out' => $session->clock_out,
                        'duration' => $session->duration_formatted,
                        'status' => $session->status,
                        'status_message' => $session->status_message,
                        'is_auto_clock_out' => $session->is_auto_clock_out,
                        'notes' => $session->notes,
                        'work_summaries' => $session->workSummaries,
                    ];
                })
            ];
            $overallTotal += $dailyMinutes;
        }
        
        return response()->json([
            'success' => true,
            'timeline' => $timeline,
            'daily_totals' => $dailyTotals,
            'overall_total_minutes' => $overallTotal,
            'overall_total_formatted' => sprintf('%02d:%02d', intval($overallTotal / 60), $overallTotal % 60),
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]);
    }

    /**
     * Add or update work summary for a session or day
     */
    public function addWorkSummary(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'summary' => 'required|string|max:2000',
            'type' => 'required|in:daily,session',
            'session_id' => 'required_if:type,session|exists:attendance_sessions,id',
        ]);
        
        try {
            $userId = $request->user()->id;
            
            if ($request->input('type') === 'session') {
                // Verify session belongs to user
                $session = AttendanceSession::where('id', $request->input('session_id'))
                    ->where('user_id', $userId)
                    ->firstOrFail();
                    
                $summary = WorkSummary::createSessionSummary(
                    $userId,
                    $request->input('date'),
                    $request->input('summary'),
                    $request->input('session_id')
                );
            } else {
                $summary = WorkSummary::createDailySummary(
                    $userId,
                    $request->input('date'),
                    $request->input('summary')
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Work summary added successfully',
                'summary' => $summary
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add work summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto clock-out missing sessions (can be called manually or via cron)
     */
    public function autoClockOut(Request $request)
    {
        try {
            $count = AttendanceSession::autoClockOutMissingSessions();
            
            return response()->json([
                'success' => true,
                'message' => "Auto clocked-out {$count} sessions",
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto clock-out: ' . $e->getMessage()
            ], 500);
        }
    }
}
