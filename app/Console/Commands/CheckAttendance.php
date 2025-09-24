<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class CheckAttendance extends Command
{
    protected $signature = 'attendance:check';
    protected $description = 'Check current attendance status';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("Checking attendance for: " . $today->format('Y-m-d'));
        
        $records = Attendance::whereDate('date', $today)->with('user')->get();
        
        $this->info("Found " . count($records) . " attendance records for today:");
        $this->line("");
        
        foreach ($records as $record) {
            $this->line("ID: {$record->id}");
            $this->line("User: {$record->user->name} (ID: {$record->user_id})");
            $this->line("Date: {$record->date}");
            $this->line("Clock In: " . ($record->clock_in ? $record->clock_in->format('H:i:s') : 'Not clocked in'));
            $this->line("Clock Out: " . ($record->clock_out ? $record->clock_out->format('H:i:s') : 'Not clocked out'));
            $this->line("Status: {$record->status}");
            $this->line("---");
        }
        
        $this->line("");
        $users = User::all();
        $this->info("Users in system:");
        foreach ($users as $user) {
            $this->line("ID: {$user->id} - {$user->name} ({$user->email})");
        }
        
        return 0;
    }
}
