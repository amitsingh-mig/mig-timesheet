<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ResetLeaveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:reset 
                           {--user-id= : Reset specific user by ID} 
                           {--force : Force reset all users on leave}
                           {--dry-run : Show what would be reset without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset leave status for users whose leave period has ended';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($userId = $this->option('user-id')) {
            return $this->resetSpecificUser($userId);
        } elseif ($this->option('force')) {
            return $this->resetAllUsers();
        } else {
            return $this->resetExpiredLeaves();
        }
    }

    /**
     * Reset specific user by ID
     */
    private function resetSpecificUser(int $userId): int
    {
        try {
            $user = User::findOrFail($userId);
            
            if ($this->option('dry-run')) {
                $this->info("DRY RUN: Would reset leave status for user: {$user->name} (ID: {$user->id})");
                $this->info("Current status: {$user->status}");
                $this->info("Leave end date: " . ($user->leave_end_date ? $user->leave_end_date->format('Y-m-d') : 'null'));
                return 0;
            }
            
            $user->update([
                'status' => 'active',
                'leave_start_date' => null,
                'leave_end_date' => null,
                'leave_reason' => null
            ]);
            
            $this->clearUserCaches($user->id);
            
            $this->info("✅ Leave status reset for user: {$user->name} (ID: {$user->id})");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error resetting user {$userId}: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Reset all users currently on leave
     */
    private function resetAllUsers(): int
    {
        $users = User::where('status', 'on_leave')->get();
        
        if ($this->option('dry-run')) {
            $this->info("DRY RUN: Would reset leave status for {$users->count()} users:");
            foreach ($users as $user) {
                $this->info("- {$user->name} (ID: {$user->id}) - Leave ends: " . 
                           ($user->leave_end_date ? $user->leave_end_date->format('Y-m-d') : 'null'));
            }
            return 0;
        }
        
        $count = 0;
        foreach ($users as $user) {
            $user->update([
                'status' => 'active',
                'leave_start_date' => null,
                'leave_end_date' => null,
                'leave_reason' => null
            ]);
            
            $this->clearUserCaches($user->id);
            $count++;
        }
            
        $this->info("✅ Force reset leave status for {$count} users");
        return 0;
    }

    /**
     * Reset only users whose leave period has expired
     */
    private function resetExpiredLeaves(): int
    {
        $users = User::where('leave_end_date', '<', now())
            ->where('status', 'on_leave')
            ->get();
        
        if ($this->option('dry-run')) {
            $this->info("DRY RUN: Would reset leave status for {$users->count()} users with expired leaves:");
            foreach ($users as $user) {
                $this->info("- {$user->name} (ID: {$user->id}) - Leave ended: " . 
                           $user->leave_end_date->format('Y-m-d'));
            }
            return 0;
        }
        
        if ($users->isEmpty()) {
            $this->info("✅ No users with expired leave found");
            return 0;
        }
        
        $count = 0;
        foreach ($users as $user) {
            $user->update([
                'status' => 'active',
                'leave_start_date' => null,
                'leave_end_date' => null,
                'leave_reason' => null
            ]);
            
            $this->clearUserCaches($user->id);
            $this->info("✅ Reset {$user->name} (leave ended: {$user->leave_end_date->format('Y-m-d')})");
            $count++;
        }
            
        $this->info("✅ Reset leave status for {$count} users with expired leaves");
        return 0;
    }

    /**
     * Clear user-specific caches
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
