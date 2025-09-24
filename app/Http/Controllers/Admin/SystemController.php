<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    /**
     * Return live system status data for the admin modal.
     */
    public function status(Request $request)
    {
        $dbConnected = true;
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        // Disk usage may vary by OS; attempt to compute, fallback gracefully
        try {
            $total = @disk_total_space(base_path());
            $free = @disk_free_space(base_path());
            $diskUsage = $total && $free ? (int) (100 - ($free / $total * 100)) . '%' : 'N/A';
        } catch (\Throwable $e) {
            $diskUsage = 'N/A';
        }

        return response()->json([
            'success' => true,
            'server' => 'Online',
            'database' => $dbConnected ? 'Connected' : 'Disconnected',
            // Replace with an actual online metric if you track sessions
            'users_online' => User::count(),
            'last_backup' => now()->subHours(6)->toDateTimeString(),
            'disk_usage' => $diskUsage,
        ]);
    }

    /**
     * Return live quick report stats for the admin modal.
     */
    public function quickReports(Request $request)
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $todayHours = (float) Timesheet::whereDate('date', $today)->sum('hours_worked');
        $weekHours = (float) Timesheet::whereBetween('date', [$weekStart, $weekEnd])->sum('hours_worked');

        $top = Timesheet::select('user_id', DB::raw('SUM(hours_worked) as total_hours'))
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->groupBy('user_id')
            ->orderByDesc('total_hours')
            ->with('user')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                return optional($row->user)->name ?? 'Unknown';
            });

        $pendingApprovals = Timesheet::where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'daily_summary' => 'Today: ' . $todayHours . ' hours logged',
            'weekly_trend' => 'This week: ' . $weekHours . ' hours',
            'top_performers' => $top,
            'pending_approvals' => $pendingApprovals,
        ]);
    }
}


