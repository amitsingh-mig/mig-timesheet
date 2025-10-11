<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Timesheet;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display the user's profile view.
     */
    public function index(Request $request, $id = null): View|RedirectResponse
    {
        // If ID is provided, get that user, otherwise get current user
        if ($id) {
            // Only accept numeric IDs, reject strings like 'edit'
            if (!is_numeric($id)) {
                abort(404);
            }
            
            $user = User::with('role')->find($id);
            if (!$user) {
                return redirect()->back()->with('error', 'User not found');
            }
            
            // Check if current user can view this profile
            $this->authorize('show', $user);
        } else {
            $user = $request->user();
        }
        
        // Calculate attendance and working hours summary
        $attendanceSummary = $this->calculateAttendanceSummary($user->id);
        
        return view('users.profile', [
            'user' => $user,
            'attendanceSummary' => $attendanceSummary
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = $request->user();
        $photo = $request->file('photo');

        // Validate image dimensions
        $imageInfo = getimagesize($photo->getPathname());
        if ($imageInfo === false) {
            return Redirect::route('profile.edit')->with('error', 'Invalid image file.');
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width > 1080 || $height > 1080) {
            return Redirect::route('profile.edit')->with('error', 'Image must be maximum 1080x1080 pixels. Current size: ' . $width . 'x' . $height . ' pixels.');
        }

        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . $user->profile_photo);
        }

        // Store new photo
        $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('profile-photos', $filename, 'public');

        // Update user record
        $user->update(['profile_photo' => $filename]);

        return Redirect::route('profile.edit')->with('status', 'photo-updated');
    }

    /**
     * Delete the user's profile photo.
     */
    public function deletePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . $user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return Redirect::route('profile.edit')->with('status', 'photo-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        // Delete profile photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . $user->profile_photo);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Calculate attendance and working hours summary for a user
     */
    private function calculateAttendanceSummary($userId)
    {
        $now = Carbon::now();
        
        // Get attendance data
        $totalAttendanceDays = Attendance::where('user_id', $userId)->count();
        $completeSessions = Attendance::where('user_id', $userId)
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->count();
        
        // Get timesheet data for hours calculation
        $totalHours = Timesheet::where('user_id', $userId)->sum('hours_worked');
        $avgHoursPerDay = $totalAttendanceDays > 0 ? $totalHours / $totalAttendanceDays : 0;

        // Get hours for different periods
        $today = $now->copy()->startOfDay();
        $thisWeek = $now->copy()->startOfWeek();
        $thisMonth = $now->copy()->startOfMonth();
        $thisYear = $now->copy()->startOfYear();

        $daysHours = Timesheet::where('user_id', $userId)
            ->whereDate('date', $today)
            ->sum('hours_worked');

        $weeksHours = Timesheet::where('user_id', $userId)
            ->where('date', '>=', $thisWeek)
            ->sum('hours_worked');

        $monthsHours = Timesheet::where('user_id', $userId)
            ->where('date', '>=', $thisMonth)
            ->sum('hours_worked');

        $yearsHours = Timesheet::where('user_id', $userId)
            ->where('date', '>=', $thisYear)
            ->sum('hours_worked');
        
        return [
            'total_attendance_days' => $totalAttendanceDays,
            'complete_sessions' => $completeSessions,
            'total_hours' => $totalHours,
            'avg_hours_per_day' => $avgHoursPerDay,
            'days_hours' => $daysHours,
            'weeks_hours' => $weeksHours,
            'months_hours' => $monthsHours,
            'years_hours' => $yearsHours,
        ];
    }
}
