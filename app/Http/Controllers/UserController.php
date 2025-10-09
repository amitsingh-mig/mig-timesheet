<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Timesheet;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserController extends Controller
{
    // API methods remain as-is below. Additional web methods added.
    public function indexPage()
    {
        $this->authorize('index', User::class);
        $users = User::orderBy('id', 'asc')->paginate(15);
        return view('users.index', ['users' => $users]);
    }

    public function showPage($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $this->authorize('show', $user);
        return view('users.show', ['user' => $user]);
    }

    public function editPage($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $this->authorize('update', $user);
        return view('users.edit', ['user' => $user]);
    }

    public function updatePage(Request $request, $id)
    {
        $validator = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $this->authorize('update', $user);

        $user->name = $validator['name'];
        $user->email = $validator['email'];
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'User updated successfully');
    }

    public function destroyPage(Request $request, $id)
    {
        $this->authorize('destroy', User::class);
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted');
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // Only admins (via can:admin middleware) reach here
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'Password reset successfully');
    }

    public function showProfile($id)
    {
        $user = User::with('role')->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Check if current user can view this profile
        $this->authorize('show', $user);

        // Calculate attendance and working hours summary
        $attendanceSummary = $this->calculateAttendanceSummary($user->id);

        return view('users.profile', [
            'user' => $user,
            'attendanceSummary' => $attendanceSummary
        ]);
    }

    private function calculateAttendanceSummary($userId)
    {
        $now = Carbon::now();
        
        // Calculate hours for different periods using the same logic as admin pages
        $daysHours = $this->getHoursForPeriod($userId, $now->copy()->startOfDay(), $now->copy()->endOfDay());
        $weeksHours = $this->getHoursForPeriod($userId, $now->copy()->startOfWeek(), $now->copy()->endOfWeek());
        $monthsHours = $this->getHoursForPeriod($userId, $now->copy()->startOfMonth(), $now->copy()->endOfMonth());
        $yearsHours = $this->getHoursForPeriod($userId, $now->copy()->startOfYear(), $now->copy()->endOfYear());

        // Get attendance records for additional statistics
        $attendances = Attendance::where('user_id', $userId)
            ->whereNotNull('clock_in')
            ->get();

        $workingDays = $attendances->count();
        $completeSessions = $attendances->where('clock_out', '!=', null)->count();
        $incompleteSessions = $attendances->where('clock_out', null)->count();

        // Calculate total hours from timesheets (more accurate than attendance)
        $totalHours = $this->getHoursForPeriod($userId, $now->copy()->startOfYear(), $now->copy()->endOfYear());
        
        // Calculate average hours per working day
        $avgHoursPerDay = $workingDays > 0 ? round($totalHours / $workingDays, 2) : 0;

        return [
            'total_hours' => round($totalHours, 2),
            'days_hours' => round($daysHours, 2),
            'weeks_hours' => round($weeksHours, 2),
            'months_hours' => round($monthsHours, 2),
            'years_hours' => round($yearsHours, 2),
            'total_attendance_days' => $workingDays,
            'complete_sessions' => $completeSessions,
            'incomplete_sessions' => $incompleteSessions,
            'avg_hours_per_day' => $avgHoursPerDay
        ];
    }

    /**
     * Get hours for a specific period (same logic as admin pages)
     */
    private function getHoursForPeriod($userId, $startDate, $endDate)
    {
        $timesheets = Timesheet::where('user_id', $userId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $totalHours = 0;
        foreach ($timesheets as $timesheet) {
            if ($timesheet->hours_worked) {
                $totalHours += (float)$timesheet->hours_worked;
            } elseif ($timesheet->hours) {
                // Convert HH:MM format to decimal hours
                if (preg_match('/^(\d+):(\d+)$/', $timesheet->hours, $matches)) {
                    $totalHours += intval($matches[1]) + (intval($matches[2]) / 60);
                } elseif (is_numeric($timesheet->hours)) {
                    $totalHours += (float)$timesheet->hours;
                }
            }
        }

        return $totalHours;
    }
    public function index()
    {
        $this->authorize('index',User::class);
        $user = User::all();

        return response()->json(['data' => $user], 200);
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }
        $this->authorize('show', $user); 
        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        //input
        $validator = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        //get user object
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $this->authorize('update',$user);

        //update user
        $user->name = $validator['name'];
        $user->email = $validator['email'];
        $user->save();

        //return response
        return response()->json($user, 200);
    }

    public function destroy($id)
    {
        $this->authorize('destroy',User::class);
        
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $user->delete();

        return response(null, 204);
    }
}
