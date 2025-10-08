<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display the user management page
     */
    public function index()
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            abort(403, 'Access denied');
        }

        return view('admin.users');
    }

    /**
     * Get users data for the table
     */
    public function getData(Request $request)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            Log::warning('Non-admin user attempted to access admin data', ['user_id' => Auth::id()]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            Log::info('Admin getData called', [
                'user_id' => Auth::id(),
                'filters' => $request->all()
            ]);
            
            // If only stats are requested
            if ($request->get('stats_only')) {
                return $this->getDashboardStats();
            }
            $query = User::with('role');
            
            // Search filter
            if ($request->get('q')) {
                $searchTerm = $request->get('q');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%');
                });
            }

            // Role filter
            if ($request->get('role')) {
                $query->whereHas('role', function($q) use ($request) {
                    $q->where('name', $request->get('role'));
                });
            }

            // Department filter
            if ($request->get('department')) {
                $query->where('department', $request->get('department'));
            }

            // Status filter (assuming you have a status column or use email_verified_at)
            if ($request->get('status')) {
                if ($request->get('status') === 'active') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($request->get('status') === 'inactive') {
                    $query->whereNull('email_verified_at');
                }
            }

            $page = $request->get('page', 1);
            $perPage = 10;
            
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedUsers = collect($users->items())->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : 'employee',
                    'department' => $user->department ?? 'General',
                    'status' => $user->email_verified_at ? 'active' : 'inactive',
                    'created_at' => $user->created_at->format('Y-m-d H:i:s')
                ];
            });

            Log::info('Admin users data prepared', [
                'total_users' => $users->total(),
                'current_page' => $users->currentPage(),
                'users_count' => $formattedUsers->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $formattedUsers,
                'users' => $formattedUsers, // Backward compatibility
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'total' => $users->total(),
                'debug' => [
                    'query_filters' => $request->all(),
                    'user_count' => $formattedUsers->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|in:admin,employee',
                'department' => 'nullable|string|max:100|in:Web,Graphic,Editorial,Multimedia,Sales,Marketing,Intern,General'
            ]);

            // Set department based on role
            $department = $validated['department'] ?? ($validated['role'] === 'admin' ? 'Admin' : 'General');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'department' => $department,
                'email_verified_at' => now()
            ]);

            // Assign role
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->role_id = $role->id;
                $user->save();
            } else {
                // Create role if it doesn't exist
                $role = Role::create(['name' => $validated['role']]);
                $user->role_id = $role->id;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $validated['role'],
                    'status' => 'active'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $user = User::findOrFail($id);
            
            // Log the incoming request data for debugging
            \Log::info('User update request data:', $request->all());
            
            $validationRules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'role' => 'required|string|in:admin,employee',
                'department' => 'nullable|string|max:100|in:Admin,Web,Graphic,Editorial,Multimedia,Sales,Marketing,Intern,General',
                'status' => 'required|string|in:active,inactive'
            ];

            // Add password validation if password is being changed
            if ($request->has('password') && $request->password) {
                $validationRules['password'] = 'required|string|min:8|confirmed';
            }

            $validated = $request->validate($validationRules);

            // Update user data
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'department' => $validated['department'] ?? ($validated['role'] === 'admin' ? 'Admin' : 'General')
            ];

            // Update email verification status based on status
            if ($validated['status'] === 'active') {
                $updateData['email_verified_at'] = now();
            } else {
                $updateData['email_verified_at'] = null;
            }

            // Update password if provided
            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Update role
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->role_id = $role->id;
                $user->save();
            } else {
                // Create role if it doesn't exist
                $role = Role::create(['name' => $validated['role']]);
                $user->role_id = $role->id;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('User update error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $validated = $request->validate([
                'password' => 'required|string|min:8|confirmed'
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $user = User::findOrFail($id);
            
            // Don't allow deleting the current admin user
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        try {
            Log::info('Loading dashboard statistics');
            
            // 1. Total Employees (exclude admin users, count only active employees)
            $totalEmployees = User::whereHas('role', function($query) {
                $query->where('name', '!=', 'admin');
            })->where('email_verified_at', '!=', null)->count();
            
            Log::info('Total employees calculated', ['count' => $totalEmployees]);
            
            // 2. Present Today (users who clocked in today)
            $today = now()->toDateString();
            $presentToday = \App\Models\Attendance::whereDate('date', $today)
                ->whereNotNull('clock_in')
                ->distinct('user_id')
                ->count();
            
            Log::info('Present today calculated', ['count' => $presentToday, 'date' => $today]);
            
            // 3. Total Hours This Month (from timesheet entries)
            $currentMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            
            // Try both hours_worked and hours fields for compatibility
            $totalHoursWorked = \App\Models\Timesheet::whereBetween('date', [$currentMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                ->sum('hours_worked');
                
            // If hours_worked is null/0, try to calculate from hours field
            if ($totalHoursWorked == 0) {
                $timesheets = \App\Models\Timesheet::whereBetween('date', [$currentMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                    ->whereNotNull('hours')
                    ->get();
                    
                $totalHoursWorked = $timesheets->sum(function($timesheet) {
                    if (is_numeric($timesheet->hours)) {
                        return (float)$timesheet->hours;
                    }
                    // Convert HH:MM format to decimal hours
                    if (preg_match('/^(\d+):(\d+)$/', $timesheet->hours, $matches)) {
                        return intval($matches[1]) + (intval($matches[2]) / 60);
                    }
                    return 0;
                });
            }
            
            Log::info('Total hours calculated', ['hours' => $totalHoursWorked, 'period' => $currentMonth->format('M Y')]);
            
            // 4. Average Attendance (percentage of employees present on working days this month)
            $workingDaysThisMonth = $this->calculateWorkingDaysThisMonth();
            $totalAttendanceRecords = \App\Models\Attendance::whereBetween('date', [$currentMonth->format('Y-m-d'), now()->format('Y-m-d')])
                ->whereNotNull('clock_in')
                ->distinct(['user_id', 'date'])
                ->count();
                
            $expectedAttendanceRecords = $totalEmployees * $workingDaysThisMonth;
            $avgAttendance = $expectedAttendanceRecords > 0 
                ? round(($totalAttendanceRecords / $expectedAttendanceRecords) * 100, 1)
                : 0;
            
            Log::info('Average attendance calculated', [
                'working_days' => $workingDaysThisMonth,
                'total_records' => $totalAttendanceRecords,
                'expected_records' => $expectedAttendanceRecords,
                'percentage' => $avgAttendance
            ]);
            
            $stats = [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'total_hours' => round($totalHoursWorked, 1),
                'avg_attendance' => $avgAttendance
            ];
            
            Log::info('Dashboard statistics prepared', $stats);
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard statistics error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage(),
                'stats' => [
                    'total_employees' => 0,
                    'present_today' => 0,
                    'total_hours' => 0,
                    'avg_attendance' => 0
                ]
            ]);
        }
    }
    
    /**
     * Calculate working days in the current month (excluding weekends)
     */
    private function calculateWorkingDaysThisMonth()
    {
        $startOfMonth = now()->startOfMonth();
        $today = now();
        $workingDays = 0;
        
        // Count working days from start of month to today
        $current = $startOfMonth->copy();
        while ($current <= $today) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }


    /**
     * Get role distribution for chart
     */
    public function getRoleDistribution()
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $roleStats = User::with('role')
                ->get()
                ->groupBy(function($user) {
                    return $user->role ? $user->role->name : 'user';
                })
                ->map(function($group) {
                    return $group->count();
                });

            $labels = $roleStats->keys()->toArray();
            $data = $roleStats->values()->toArray();

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load role distribution: ' . $e->getMessage(),
                'labels' => ['Admin', 'User'],
                'data' => [0, 0]
            ]);
        }
    }

    /**
     * Get employee time overview data
     */
    public function employeeTimeOverview(Request $request)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            Log::warning('Non-admin user attempted to access employee time overview', ['user_id' => Auth::id()]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            Log::info('Employee time overview requested', [
                'user_id' => Auth::id(),
                'filters' => $request->all()
            ]);

            $query = \App\Models\Timesheet::with('user');

            // Apply filters
            if ($request->get('employee_id')) {
                $query->where('user_id', $request->get('employee_id'));
            }

            if ($request->get('department')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('department', $request->get('department'));
                });
            }

            if ($request->get('start_date')) {
                $query->whereDate('date', '>=', $request->get('start_date'));
            }

            if ($request->get('end_date')) {
                $query->whereDate('date', '<=', $request->get('end_date'));
            }

            $page = $request->get('page', 1);
            $perPage = 10;
            
            $timeLogs = $query->orderBy('date', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $formattedLogs = $timeLogs->map(function($log) {
                return [
                    'id' => $log->id,
                    'employee_name' => $log->user->name,
                    'employee_email' => $log->user->email,
                    'date' => $log->date instanceof \Carbon\Carbon ? $log->date->format('Y-m-d') : $log->date,
                    'clock_in' => $log->start_time ? (is_string($log->start_time) ? substr($log->start_time, 0, 5) : \Carbon\Carbon::parse($log->start_time)->format('H:i')) : null,
                    'clock_out' => $log->end_time ? (is_string($log->end_time) ? substr($log->end_time, 0, 5) : \Carbon\Carbon::parse($log->end_time)->format('H:i')) : null,
                    'total_hours' => $log->hours_worked ?? 0,
                    'tasks_completed' => 1 // Each timesheet entry counts as 1 task
                ];
            });

            Log::info('Employee time overview data prepared', [
                'total_logs' => $timeLogs->total(),
                'current_page' => $timeLogs->currentPage(),
                'logs_count' => $formattedLogs->count()
            ]);

            return response()->json([
                'success' => true,
                'timeLogs' => $formattedLogs,
                'current_page' => $timeLogs->currentPage(),
                'total_pages' => $timeLogs->lastPage(),
                'total' => $timeLogs->total()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load time logs: ' . $e->getMessage(),
                'timeLogs' => [],
                'current_page' => 1,
                'total_pages' => 1,
                'total' => 0
            ]);
        }
    }

    /**
     * Export Employee Time Overview to CSV honoring filters
     */
    public function exportTimeCsv(Request $request)
    {
        // Admin check
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            abort(403);
        }

        $employeeId = $request->get('employee_id');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $query = \App\Models\Timesheet::with('user');
        if ($employeeId) $query->where('user_id', $employeeId);
        if ($startDate)  $query->whereDate('date', '>=', $startDate);
        if ($endDate)    $query->whereDate('date', '<=', $endDate);

        $rows = $query->orderBy('date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_time_export_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee','Email','Date','Clock In','Clock Out','Total Hours','Description']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    optional($r->user)->name,
                    optional($r->user)->email,
                    (string) $r->date,
                    $r->start_time,
                    $r->end_time,
                    (float) ($r->hours_worked ?? 0),
                    $r->description ?? $r->task ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get time chart data for different periods
     */
    public function getTimeChartData(Request $request)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $period = $request->get('period', 'week');
            $employeeId = $request->get('employee_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = \App\Models\Timesheet::query();

            if ($employeeId) {
                $query->where('user_id', $employeeId);
            }

            if ($startDate) {
                $query->where('date', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('date', '<=', $endDate);
            }

            switch ($period) {
                case 'day':
                    $data = $query->selectRaw('DATE(date) as label, SUM(hours_worked) as total')
                        ->groupBy('label')
                        ->orderBy('label')
                        ->get();
                    break;
                case 'week':
                    $data = $query->selectRaw('YEARWEEK(date) as week_num, SUM(hours_worked) as total')
                        ->groupBy('week_num')
                        ->orderBy('week_num')
                        ->get()
                        ->map(function($item) {
                            $item->label = 'Week ' . substr($item->week_num, -2);
                            return $item;
                        });
                    break;
                case 'month':
                    $data = $query->selectRaw('MONTH(date) as month_num, YEAR(date) as year_num, SUM(hours_worked) as total')
                        ->groupBy(['year_num', 'month_num'])
                        ->orderBy('year_num')
                        ->orderBy('month_num')
                        ->get()
                        ->map(function($item) {
                            $item->label = date('M Y', mktime(0, 0, 0, $item->month_num, 1, $item->year_num));
                            return $item;
                        });
                    break;
                case 'year':
                    $data = $query->selectRaw('YEAR(date) as label, SUM(hours_worked) as total')
                        ->groupBy('label')
                        ->orderBy('label')
                        ->get();
                    break;
            }

            $labels = $data->pluck('label')->toArray();
            $values = $data->pluck('total')->map(function($value) {
                return round((float)$value, 1);
            })->toArray();

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'data' => $values
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load chart data: ' . $e->getMessage(),
                'labels' => [],
                'data' => []
            ]);
        }
    }

    /**
     * Get detailed time log information
     */
    public function getTimeDetails($id)
    {
        // Check if user is admin
        if (!Auth::user() || !Auth::user()->role || Auth::user()->role->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $timeLog = \App\Models\Timesheet::with('user')
                ->findOrFail($id);

            $details = [
                'id' => $timeLog->id,
                'employee_name' => $timeLog->user->name,
                'employee_email' => $timeLog->user->email,
                'date' => $timeLog->date,
                'clock_in' => $timeLog->start_time ? \Carbon\Carbon::parse($timeLog->start_time)->format('H:i A') : null,
                'clock_out' => $timeLog->end_time ? \Carbon\Carbon::parse($timeLog->end_time)->format('H:i A') : null,
                'total_hours' => $timeLog->hours_worked ?? 0,
                'tasks' => [
                    [
                        'title' => 'Daily tasks completed',
                        'description' => $timeLog->description ?? 'No description available',
                        'completed_at' => $timeLog->updated_at ? $timeLog->updated_at->format('H:i A') : 'N/A'
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'details' => $details
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load time details: ' . $e->getMessage()
            ]);
        }
    }
}
