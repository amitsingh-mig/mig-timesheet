<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\TimesheetApproval;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimesheetCalendarController extends Controller
{
    /**
     * Display the admin timesheet calendar
     */
    public function index()
    {
        // Check admin permission
        if (!Auth::user() || !Auth::user()->role || !in_array(Auth::user()->role->name, ['admin', 'manager'])) {
            abort(403, 'Access denied');
        }
        
        return view('admin.timesheet-calendar.index');
    }
    
    /**
     * Get calendar data for the specified month
     */
    public function getCalendarData(Request $request)
    {
        try {
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);
            $view = $request->get('view', 'month'); // day, week, month
            $userId = $request->get('user_id');
            $status = $request->get('status');
            
            Log::info('Calendar data requested', [
                'month' => $month,
                'year' => $year,
                'view' => $view,
                'user_id' => $userId,
                'status' => $status
            ]);
            
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            // Adjust date range based on view
            if ($view === 'week') {
                $startDate = $startDate->startOfWeek();
                $endDate = $endDate->endOfWeek();
            }
            
            $query = Timesheet::with(['user', 'approvals.admin'])
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                
            // Apply filters
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $timesheets = $query->get();
            
            // Group by date
            $calendarData = $timesheets->groupBy(function($timesheet) {
                return $timesheet->date instanceof Carbon 
                    ? $timesheet->date->format('Y-m-d') 
                    : Carbon::parse($timesheet->date)->format('Y-m-d');
            })->map(function($dayTimesheets, $date) {
                return [
                    'date' => $date,
                    'timesheets' => $dayTimesheets->map(function($timesheet) {
                        return [
                            'id' => $timesheet->id,
                            'user_id' => $timesheet->user_id,
                            'user_name' => optional($timesheet->user)->name ?? 'Unknown',
                            'hours' => (float) ($timesheet->hours_worked ?? 0),
                            'status' => $timesheet->status ?? 'pending',
                            'is_overtime' => (bool) ($timesheet->is_overtime ?? false),
                            'location' => $timesheet->location,
                            'description' => $timesheet->description ?? $timesheet->task
                        ];
                    }),
                    'summary' => [
                        'total_hours' => (float) $dayTimesheets->sum('hours_worked'),
                        'total_employees' => $dayTimesheets->unique('user_id')->count(),
                        'pending_count' => $dayTimesheets->where('status', 'pending')->count(),
                        'approved_count' => $dayTimesheets->where('status', 'approved')->count(),
                        'rejected_count' => $dayTimesheets->where('status', 'rejected')->count(),
                        'overtime_count' => $dayTimesheets->where('is_overtime', true)->count(),
                        'missing_count' => $this->getMissingEntriesCount($date)
                    ]
                ];
            });
            
            // Fill in missing dates with empty data
            $calendar = [];
            $current = $startDate->copy();
            
            while ($current->lte($endDate)) {
                $dateStr = $current->format('Y-m-d');
                $calendar[$dateStr] = $calendarData->get($dateStr, [
                    'date' => $dateStr,
                    'timesheets' => [],
                    'summary' => [
                        'total_hours' => 0,
                        'total_employees' => 0,
                        'pending_count' => 0,
                        'approved_count' => 0,
                        'rejected_count' => 0,
                        'overtime_count' => 0,
                        'missing_count' => $this->getMissingEntriesCount($dateStr)
                    ]
                ]);
                $current->addDay();
            }
            
            return response()->json([
                'success' => true,
                'calendar_data' => array_values($calendar),
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'month' => $month,
                    'year' => $year,
                    'view' => $view
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Calendar data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load calendar data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get detailed day information
     */
    public function getDayDetails(Request $request, $date)
    {
        try {
            $timesheets = Timesheet::with(['user', 'approvals.admin'])
                ->whereDate('date', $date)
                ->orderBy('user_id')
                ->get();
                
            $formattedTimesheets = $timesheets->map(function($timesheet) {
                return [
                    'id' => $timesheet->id,
                    'user_id' => $timesheet->user_id,
                    'user_name' => optional($timesheet->user)->name ?? 'Unknown',
                    'user_email' => optional($timesheet->user)->email,
                    'date' => $timesheet->date,
                    'clock_in' => $timesheet->start_time,
                    'clock_out' => $timesheet->end_time,
                    'hours' => (float) ($timesheet->hours_worked ?? 0),
                    'status' => $timesheet->status ?? 'pending',
                    'is_overtime' => (bool) ($timesheet->is_overtime ?? false),
                    'location' => $timesheet->location,
                    'description' => $timesheet->description ?? $timesheet->task,
                    'notes' => $timesheet->notes,
                    'submitted_at' => $timesheet->submitted_at,
                    'approved_by' => $timesheet->approved_by ? optional($timesheet->approver)->name : null,
                    'approved_at' => $timesheet->approved_at,
                    'rejection_reason' => $timesheet->rejection_reason
                ];
            });
            
            return response()->json([
                'success' => true,
                'date' => $date,
                'timesheets' => $formattedTimesheets,
                'summary' => [
                    'total_entries' => $timesheets->count(),
                    'total_hours' => (float) $timesheets->sum('hours_worked'),
                    'pending_count' => $timesheets->where('status', 'pending')->count(),
                    'approved_count' => $timesheets->where('status', 'approved')->count(),
                    'rejected_count' => $timesheets->where('status', 'rejected')->count(),
                    'overtime_count' => $timesheets->where('is_overtime', true)->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Day details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load day details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Approve a timesheet entry
     */
    public function approveTimesheet(Request $request, $id)
    {
        try {
            $timesheet = Timesheet::findOrFail($id);
            $reason = $request->get('reason', '');
            
            DB::transaction(function() use ($timesheet, $reason) {
                // Update timesheet
                $timesheet->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
                
                // Log approval
                TimesheetApproval::create([
                    'timesheet_id' => $timesheet->id,
                    'admin_id' => Auth::id(),
                    'action' => 'approved',
                    'reason' => $reason
                ]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet approved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Approve timesheet error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject a timesheet entry
     */
    public function rejectTimesheet(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);
            
            $timesheet = Timesheet::findOrFail($id);
            $reason = $request->get('reason');
            
            DB::transaction(function() use ($timesheet, $reason) {
                // Update timesheet
                $timesheet->update([
                    'status' => 'rejected',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_reason' => $reason
                ]);
                
                // Log rejection
                TimesheetApproval::create([
                    'timesheet_id' => $timesheet->id,
                    'admin_id' => Auth::id(),
                    'action' => 'rejected',
                    'reason' => $reason
                ]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet rejected successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Reject timesheet error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk approve timesheets
     */
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'timesheet_ids' => 'required|array',
                'timesheet_ids.*' => 'integer|exists:timesheets,id'
            ]);
            
            $timesheetIds = $request->get('timesheet_ids');
            $reason = $request->get('reason', 'Bulk approval');
            
            DB::transaction(function() use ($timesheetIds, $reason) {
                // Update all timesheets
                Timesheet::whereIn('id', $timesheetIds)
                    ->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]);
                
                // Log bulk approval
                foreach ($timesheetIds as $timesheetId) {
                    TimesheetApproval::create([
                        'timesheet_id' => $timesheetId,
                        'admin_id' => Auth::id(),
                        'action' => 'approved',
                        'reason' => $reason
                    ]);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => count($timesheetIds) . ' timesheets approved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Bulk approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve timesheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk reject timesheets
     */
    public function bulkReject(Request $request)
    {
        try {
            $request->validate([
                'timesheet_ids' => 'required|array',
                'timesheet_ids.*' => 'integer|exists:timesheets,id',
                'reason' => 'required|string|max:500'
            ]);
            
            $timesheetIds = $request->get('timesheet_ids');
            $reason = $request->get('reason');
            
            DB::transaction(function() use ($timesheetIds, $reason) {
                // Update all timesheets
                Timesheet::whereIn('id', $timesheetIds)
                    ->update([
                        'status' => 'rejected',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'rejection_reason' => $reason
                    ]);
                
                // Log bulk rejection
                foreach ($timesheetIds as $timesheetId) {
                    TimesheetApproval::create([
                        'timesheet_id' => $timesheetId,
                        'admin_id' => Auth::id(),
                        'action' => 'rejected',
                        'reason' => $reason
                    ]);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => count($timesheetIds) . ' timesheets rejected successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Bulk reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject timesheets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
            
            $timesheets = Timesheet::whereBetween('date', [$startDate, $endDate])->get();
            
            return response()->json([
                'success' => true,
                'statistics' => [
                    'total_entries' => $timesheets->count(),
                    'total_hours' => $timesheets->sum('hours_worked'),
                    'pending_count' => $timesheets->where('status', 'pending')->count(),
                    'approved_count' => $timesheets->where('status', 'approved')->count(),
                    'rejected_count' => $timesheets->where('status', 'rejected')->count(),
                    'overtime_entries' => $timesheets->where('is_overtime', true)->count(),
                    'unique_employees' => $timesheets->unique('user_id')->count(),
                    'avg_hours_per_day' => $timesheets->groupBy('date')->avg(function($dayEntries) {
                        return $dayEntries->sum('hours_worked');
                    })
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }

    /**
     * Export current calendar view to CSV
     */
    public function export(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $userId = $request->get('user_id');
        $status = $request->get('status');

        $start = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $query = Timesheet::with('user')->whereBetween('date', [$start, $end]);
        if ($userId) $query->where('user_id', $userId);
        if ($status) $query->where('status', $status);
        $rows = $query->orderBy('date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="timesheet_calendar_export_' . $year . '_' . str_pad($month,2,'0',STR_PAD_LEFT) . '.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Employee','Hours','Status','Overtime','Description']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    (string) $r->date,
                    optional($r->user)->name,
                    (float) ($r->hours_worked ?? 0),
                    $r->status ?? 'pending',
                    $r->is_overtime ? 'Yes' : 'No',
                    $r->description ?? $r->task ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get missing entries count for a specific date
     */
    private function getMissingEntriesCount($date)
    {
        // Get all active users
        $activeUsersCount = User::whereHas('role', function($query) {
            $query->where('name', '!=', 'admin');
        })->count();
        
        // Get users who submitted timesheets for this date
        $submittedCount = Timesheet::whereDate('date', $date)
            ->distinct('user_id')
            ->count();
        
        return max(0, $activeUsersCount - $submittedCount);
    }
}
