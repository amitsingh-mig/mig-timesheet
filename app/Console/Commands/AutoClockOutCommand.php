<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSession;
use Carbon\Carbon;

class AutoClockOutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-clock-out {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out sessions that are missing clock-out at 11:59 PM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Running auto clock-out process...');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Find sessions that need auto clock-out
        $yesterday = Carbon::yesterday();
        $activeSessions = AttendanceSession::active()
            ->where('date', '<', Carbon::today())
            ->with('user')
            ->get();
            
        if ($activeSessions->count() === 0) {
            $this->info('No sessions found that require auto clock-out.');
            return 0;
        }
        
        $this->info("Found {$activeSessions->count()} session(s) requiring auto clock-out:");
        
        $table = [];
        foreach ($activeSessions as $session) {
            $table[] = [
                'User' => $session->user->name,
                'Date' => $session->date->format('Y-m-d'),
                'Clock In' => $session->clock_in->format('H:i:s'),
                'Session #' => $session->session_order,
                'Duration' => $session->duration_formatted,
            ];
        }
        
        $this->table(['User', 'Date', 'Clock In', 'Session #', 'Duration'], $table);
        
        if ($dryRun) {
            $this->info('DRY RUN: Would auto clock-out the above sessions at 23:59:00');
            return 0;
        }
        
        // Confirm before proceeding
        if (!$this->confirm('Do you want to auto clock-out these sessions?')) {
            $this->info('Operation cancelled.');
            return 1;
        }
        
        // Perform auto clock-out
        $count = AttendanceSession::autoClockOutMissingSessions();
        
        $this->info("Successfully auto clocked-out {$count} session(s).");
        
        // Show summary
        if ($count > 0) {
            $this->info('All sessions have been marked with auto clock-out at 23:59:00 and flagged as system-generated.');
        }
        
        return 0;
    }
}
