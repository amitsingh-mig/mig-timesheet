<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LeaveResetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resetUsers = User::where('leave_end_date', '<', now())
            ->where('status', 'on_leave')
            ->get();

        foreach ($resetUsers as $user) {
            // Reset leave status
            $user->update([
                'status' => 'active',
                'leave_start_date' => null,
                'leave_end_date' => null,
                'leave_reason' => null
            ]);

            // Clear user-specific caches
            $this->clearUserCaches($user->id);
            
            Log::info("Leave status reset for user: {$user->name} (ID: {$user->id})");
        }

        if ($resetUsers->count() > 0) {
            Log::info("Leave reset job completed. {$resetUsers->count()} users processed.");
        }
    }

    /**
     * Clear all user-specific caches
     */
    private function clearUserCaches(int $userId): void
    {
        $cacheKeys = [
            "permissions_user_{$userId}",
            "user_{$userId}_timesheets",
            "user_{$userId}_attendance",
            "user_{$userId}_summaries",
            "timesheet_summary_{$userId}",
            "attendance_calendar_{$userId}",
            "daily_summaries_{$userId}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
