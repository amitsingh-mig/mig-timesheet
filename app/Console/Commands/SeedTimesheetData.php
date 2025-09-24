<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Timesheet;
use Carbon\Carbon;

class SeedTimesheetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-timesheet-data {--user-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed sample timesheet data for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $user = User::first();
            if (!$user) {
                $this->error('No users found in the database.');
                return 1;
            }
        }

        $this->info("Creating sample timesheet data for user: {$user->name}");

        // Clear existing timesheet data for this user
        Timesheet::where('user_id', $user->id)->delete();
        $this->info('Cleared existing timesheet data.');

        $tasks = [
            'Development work on new features',
            'Bug fixes and testing',
            'Client meeting and requirements gathering',
            'Code review and documentation',
            'Database optimization and queries',
            'Frontend UI improvements',
            'API integration and testing',
            'Planning and project management'
        ];

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(30); // Last 30 days

        for ($i = 0; $i < 25; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Skip weekends occasionally
            if ($date->isWeekend() && rand(1, 3) == 1) {
                continue;
            }

            // Randomize work hours
            $startHour = rand(8, 10);
            $startMinute = rand(0, 59);
            $workHours = rand(6, 10); // 6-10 hours of work
            $workMinutes = rand(0, 59);
            
            $clockIn = sprintf('%02d:%02d', $startHour, $startMinute);
            $clockOut = Carbon::createFromFormat('H:i', $clockIn)
                ->addHours($workHours)
                ->addMinutes($workMinutes)
                ->format('H:i');
            
            $duration = Carbon::createFromFormat('H:i', $clockOut)
                ->diffInMinutes(Carbon::createFromFormat('H:i', $clockIn)) / 60;
            
            $hoursFormatted = sprintf('%02d:%02d', intval($duration), round(($duration - intval($duration)) * 60));

            Timesheet::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $clockIn,
                'end_time' => $clockOut,
                'hours_worked' => round($duration, 2),
                'description' => $tasks[array_rand($tasks)],
                'task' => $tasks[array_rand($tasks)], // For backward compatibility
                'hours' => $hoursFormatted
            ]);
        }

        $count = Timesheet::where('user_id', $user->id)->count();
        $this->info("Created {$count} sample timesheet entries for {$user->name}");
        
        return 0;
    }
}
