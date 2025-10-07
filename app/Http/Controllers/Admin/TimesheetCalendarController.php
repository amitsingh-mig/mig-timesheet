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
            $view = $request->get('view', 'month'); // day, week, month, year
            $userId = $request->get('user_id');
            
            Log::info('Calendar data requested', [
                'month' => $month,
                'year' => $year,
                'view' => $view,
                'user_id' => $userId
            ]);
            
            // Calculate date range based on view
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            // For debugging - let's also check if we have any timesheets at all
            $totalTimesheets = Timesheet::count();
            Log::info('Total timesheets in database: ' . $totalTimesheets);
            
            if ($view === 'week') {
                $startDate = $startDate->startOfWeek();
                $endDate = $endDate->endOfWeek();
            } elseif ($view === 'day') {
                $startDate = Carbon::today();
                $endDate = Carbon::today();
            } elseif ($view === 'year') {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
            }
            
            // Get timesheet data with user information
            $query = Timesheet::with(['user'])
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                
            // Apply user filter
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $timesheets = $query->orderBy('date', 'desc')->get();
            
            // Group by date and format data for calendar display
            $calendarData = $timesheets->groupBy(function($timesheet) {
                return $timesheet->date instanceof Carbon 
                    ? $timesheet->date->format('Y-m-d') 
                    : Carbon::parse($timesheet->date)->format('Y-m-d');
            })->map(function($dayTimesheets, $date) {
                return [
                    'date' => $date,
                        'timesheets' => $dayTimesheets->map(function($timesheet) {
                            // Handle both hours formats - convert time format (HH:MM:SS) to decimal
                            $hoursWorked = 0;
                            if ($timesheet->hours_worked) {
                                $hoursWorked = (float) $timesheet->hours_worked;
                            } elseif ($timesheet->hours) {
                                // Convert time format to decimal hours
                                if (is_string($timesheet->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $timesheet->hours, $matches)) {
                                    $hours = (int)$matches[1];
                                    $minutes = (int)$matches[2];
                                    $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                                    $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                                } else {
                                    $hoursWorked = (float) $timesheet->hours;
                                }
                            }
                            
                            $expectedHours = (float) ($timesheet->expected_hours ?? 8.0);
                            $overtimeHours = $timesheet->is_overtime ? max(0, $hoursWorked - $expectedHours) : 0;
                            
                            return [
                                'id' => $timesheet->id,
                                'user_id' => $timesheet->user_id,
                                'user_name' => optional($timesheet->user)->name ?? 'Unknown',
                                'user_email' => optional($timesheet->user)->email ?? '',
                                'hours' => $hoursWorked,
                                'regular_hours' => min($hoursWorked, $expectedHours),
                                'overtime_hours' => $overtimeHours,
                                'expected_hours' => $expectedHours,
                                'start_time' => $timesheet->start_time,
                                'end_time' => $timesheet->end_time,
                                'status' => $timesheet->status ?? 'draft',
                                'is_overtime' => (bool) ($timesheet->is_overtime ?? false),
                                'overtime_rate' => 1.5, // Standard overtime rate
                                'location' => $timesheet->location,
                                'description' => $timesheet->description ?? $timesheet->task ?? '',
                                'notes' => $timesheet->notes,
                                'submitted_at' => $timesheet->submitted_at,
                                'approved_at' => $timesheet->approved_at,
                                'approved_by' => $timesheet->approved_by ? optional($timesheet->approver)->name : null,
                                'rejection_reason' => $timesheet->rejection_reason
                            ];
                        }),
                    'summary' => [
                        'total_hours' => (float) $dayTimesheets->sum(function($t) { 
                            if ($t->hours_worked) {
                                return (float) $t->hours_worked;
                            } elseif ($t->hours) {
                                // Convert time format to decimal hours
                                if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                                    $hours = (int)$matches[1];
                                    $minutes = (int)$matches[2];
                                    $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                                    return $hours + ($minutes / 60) + ($seconds / 3600);
                                } else {
                                    return (float) $t->hours;
                                }
                            }
                            return 0;
                        }),
                        'regular_hours' => (float) $dayTimesheets->sum(function($t) {
                            $hoursWorked = 0;
                            if ($t->hours_worked) {
                                $hoursWorked = (float) $t->hours_worked;
                            } elseif ($t->hours) {
                                if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                                    $hours = (int)$matches[1];
                                    $minutes = (int)$matches[2];
                                    $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                                    $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                                } else {
                                    $hoursWorked = (float) $t->hours;
                                }
                            }
                            $expectedHours = (float) ($t->expected_hours ?? 8.0);
                            return min($hoursWorked, $expectedHours);
                        }),
                        'overtime_hours' => (float) $dayTimesheets->sum(function($t) {
                            if (!$t->is_overtime) return 0;
                            $hoursWorked = 0;
                            if ($t->hours_worked) {
                                $hoursWorked = (float) $t->hours_worked;
                            } elseif ($t->hours) {
                                if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                                    $hours = (int)$matches[1];
                                    $minutes = (int)$matches[2];
                                    $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                                    $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                                } else {
                                    $hoursWorked = (float) $t->hours;
                                }
                            }
                            $expectedHours = (float) ($t->expected_hours ?? 8.0);
                            return max(0, $hoursWorked - $expectedHours);
                        }),
                        'total_employees' => $dayTimesheets->unique('user_id')->count(),
                        'pending_count' => $dayTimesheets->where('status', 'pending')->count(),
                        'approved_count' => $dayTimesheets->where('status', 'approved')->count(),
                        'rejected_count' => $dayTimesheets->where('status', 'rejected')->count(),
                        'draft_count' => $dayTimesheets->where('status', 'draft')->count(),
                        'overtime_count' => $dayTimesheets->where('is_overtime', true)->count(),
                        'overtime_entries' => $dayTimesheets->where('is_overtime', true)->count(),
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
            
            // Get comprehensive employee statistics
            $employeeStats = $this->getEmployeeStatistics($startDate, $endDate, $userId);
            
            return response()->json([
                'success' => true,
                'calendar_data' => array_values($calendar),
                'employee_statistics' => $employeeStats,
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
    
    /**
     * Get comprehensive employee statistics for the period
     */
    private function getEmployeeStatistics($startDate, $endDate, $userId = null)
    {
        try {
            $query = Timesheet::with('user')
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $timesheets = $query->get();
            
            // Calculate comprehensive statistics
            $totalHours = $timesheets->sum(function($t) {
                if ($t->hours_worked) {
                    return (float) $t->hours_worked;
                } elseif ($t->hours) {
                    if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                        $hours = (int)$matches[1];
                        $minutes = (int)$matches[2];
                        $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                        return $hours + ($minutes / 60) + ($seconds / 3600);
                    } else {
                        return (float) $t->hours;
                    }
                }
                return 0;
            });
            
            $regularHours = $timesheets->sum(function($t) {
                $hoursWorked = 0;
                if ($t->hours_worked) {
                    $hoursWorked = (float) $t->hours_worked;
                } elseif ($t->hours) {
                    if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                        $hours = (int)$matches[1];
                        $minutes = (int)$matches[2];
                        $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                        $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                    } else {
                        $hoursWorked = (float) $t->hours;
                    }
                }
                $expectedHours = (float) ($t->expected_hours ?? 8.0);
                return min($hoursWorked, $expectedHours);
            });
            
            $overtimeHours = $timesheets->sum(function($t) {
                if (!$t->is_overtime) return 0;
                $hoursWorked = 0;
                if ($t->hours_worked) {
                    $hoursWorked = (float) $t->hours_worked;
                } elseif ($t->hours) {
                    if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                        $hours = (int)$matches[1];
                        $minutes = (int)$matches[2];
                        $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                        $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                    } else {
                        $hoursWorked = (float) $t->hours;
                    }
                }
                $expectedHours = (float) ($t->expected_hours ?? 8.0);
                return max(0, $hoursWorked - $expectedHours);
            });
            
            $uniqueEmployees = $timesheets->unique('user_id')->count();
            
            $statusCounts = [
                'draft' => $timesheets->where('status', 'draft')->count(),
                'pending' => $timesheets->where('status', 'pending')->count(),
                'approved' => $timesheets->where('status', 'approved')->count(),
                'rejected' => $timesheets->where('status', 'rejected')->count(),
            ];
            
            $overtimeEntries = $timesheets->where('is_overtime', true)->count();
            
            // Get employee breakdown
            $employeeBreakdown = $timesheets->groupBy('user_id')->map(function($userTimesheets) {
                $user = $userTimesheets->first()->user;
                $userTotalHours = $userTimesheets->sum(function($t) {
                    if ($t->hours_worked) {
                        return (float) $t->hours_worked;
                    } elseif ($t->hours) {
                        if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                            $hours = (int)$matches[1];
                            $minutes = (int)$matches[2];
                            $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                            return $hours + ($minutes / 60) + ($seconds / 3600);
                        } else {
                            return (float) $t->hours;
                        }
                    }
                    return 0;
                });
                $userOvertimeHours = $userTimesheets->sum(function($t) {
                    if (!$t->is_overtime) return 0;
                    $hoursWorked = 0;
                    if ($t->hours_worked) {
                        $hoursWorked = (float) $t->hours_worked;
                    } elseif ($t->hours) {
                        if (is_string($t->hours) && preg_match('/^(\d{1,2}):(\d{2}):?(\d{2})?$/', $t->hours, $matches)) {
                            $hours = (int)$matches[1];
                            $minutes = (int)$matches[2];
                            $seconds = isset($matches[3]) ? (int)$matches[3] : 0;
                            $hoursWorked = $hours + ($minutes / 60) + ($seconds / 3600);
                        } else {
                            $hoursWorked = (float) $t->hours;
                        }
                    }
                    $expectedHours = (float) ($t->expected_hours ?? 8.0);
                    return max(0, $hoursWorked - $expectedHours);
                });
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_hours' => round($userTotalHours, 2),
                    'overtime_hours' => round($userOvertimeHours, 2),
                    'regular_hours' => round($userTotalHours - $userOvertimeHours, 2),
                    'entries_count' => $userTimesheets->count(),
                    'overtime_entries' => $userTimesheets->where('is_overtime', true)->count(),
                    'status_breakdown' => [
                        'draft' => $userTimesheets->where('status', 'draft')->count(),
                        'pending' => $userTimesheets->where('status', 'pending')->count(),
                        'approved' => $userTimesheets->where('status', 'approved')->count(),
                        'rejected' => $userTimesheets->where('status', 'rejected')->count(),
                    ]
                ];
            })->values();
            
            return [
                'total_hours' => round($totalHours, 2),
                'regular_hours' => round($regularHours, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'unique_employees' => $uniqueEmployees,
                'total_entries' => $timesheets->count(),
                'overtime_entries' => $overtimeEntries,
                'status_breakdown' => $statusCounts,
                'employee_breakdown' => $employeeBreakdown,
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'days' => $startDate->diffInDays($endDate) + 1
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Employee statistics error: ' . $e->getMessage());
            return [
                'total_hours' => 0,
                'regular_hours' => 0,
                'overtime_hours' => 0,
                'unique_employees' => 0,
                'total_entries' => 0,
                'overtime_entries' => 0,
                'status_breakdown' => ['draft' => 0, 'submitted' => 0, 'approved' => 0, 'rejected' => 0],
                'employee_breakdown' => [],
                'period' => ['start' => $startDate->format('Y-m-d'), 'end' => $endDate->format('Y-m-d'), 'days' => 0]
            ];
        }
    }
    
    /**
     * Get employee time data for calendar integration
     */
    public function getEmployeeTimeData(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
            $employeeId = $request->get('employee_id');
            
            $query = Timesheet::with('user')
                ->whereBetween('date', [$startDate, $endDate]);
                
            if ($employeeId) {
                $query->where('user_id', $employeeId);
            }
            
            $timesheets = $query->orderBy('date', 'desc')->get();
            
            $formattedData = $timesheets->map(function($timesheet) {
                $hoursWorked = (float) ($timesheet->hours_worked ?? $timesheet->hours ?? 0);
                $expectedHours = (float) ($timesheet->expected_hours ?? 8.0);
                $overtimeHours = $timesheet->is_overtime ? max(0, $hoursWorked - $expectedHours) : 0;
                
                return [
                    'id' => $timesheet->id,
                    'employee_id' => $timesheet->user_id,
                    'employee_name' => $timesheet->user->name,
                    'employee_email' => $timesheet->user->email,
                    'date' => $timesheet->date->format('Y-m-d'),
                    'clock_in' => $timesheet->start_time,
                    'clock_out' => $timesheet->end_time,
                    'total_hours' => $hoursWorked,
                    'regular_hours' => min($hoursWorked, $expectedHours),
                    'overtime_hours' => $overtimeHours,
                    'expected_hours' => $expectedHours,
                    'is_overtime' => (bool) $timesheet->is_overtime,
                    'status' => $timesheet->status ?? 'draft',
                    'location' => $timesheet->location,
                    'description' => $timesheet->description ?? $timesheet->task,
                    'notes' => $timesheet->notes,
                    'submitted_at' => $timesheet->submitted_at,
                    'approved_at' => $timesheet->approved_at,
                    'approved_by' => $timesheet->approved_by ? optional($timesheet->approver)->name : null,
                    'rejection_reason' => $timesheet->rejection_reason
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'total' => $formattedData->count(),
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Employee time data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load employee time data: ' . $e->getMessage()
            ], 500);
        }
    }
}
