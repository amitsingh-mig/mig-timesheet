<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Timesheet;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users if they don't exist
        $user = User::firstOrCreate(
            ['email' => 'amit@example.com'],
            [
                'name' => 'Amit Kumar',
                'email' => 'amit@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Get the current date and calculate date ranges
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfWeek = $today->copy()->startOfWeek();

        // Sample tasks for variety
        $tasks = [
            'Frontend Development',
            'Backend API Work',
            'Code Review',
            'Team Meeting',
            'Bug Fixes',
            'Database Optimization',
            'Testing & QA',
            'Documentation',
            'Client Communication',
            'Research & Planning',
            'UI/UX Design',
            'DevOps Tasks',
            'Performance Optimization',
            'Security Updates',
            'Feature Implementation'
        ];

        // Clear existing sample data for this user
        Timesheet::where('user_id', $user->id)->delete();
        Attendance::where('user_id', $user->id)->delete();

        echo "Creating sample data for user: {$user->name}\n";

        // Generate timesheet entries for the current month
        $cursor = $startOfMonth->copy();
        $workDaysCount = 0;

        while ($cursor->lte($today)) {
            // Skip weekends for most entries (but occasionally include some)
            $isWeekend = $cursor->isWeekend();
            $shouldWork = $isWeekend ? (rand(1, 10) <= 2) : (rand(1, 10) <= 9); // 20% chance on weekends, 90% on weekdays

            if ($shouldWork) {
                // Generate 2-5 tasks per day
                $tasksPerDay = rand(2, 5);
                $totalHours = 0;

                for ($i = 0; $i < $tasksPerDay && $totalHours < 9; $i++) {
                    $task = $tasks[array_rand($tasks)];
                    
                    // Generate realistic hours (30 minutes to 4 hours per task)
                    $minutes = [30, 45, 60, 90, 120, 150, 180, 210, 240][array_rand([30, 45, 60, 90, 120, 150, 180, 210, 240])];
                    
                    // Don't exceed reasonable daily limits
                    if ($totalHours * 60 + $minutes > 540) { // Max 9 hours per day
                        $minutes = 540 - ($totalHours * 60);
                        if ($minutes < 30) break;
                    }

                    $hours = intval($minutes / 60);
                    $remainingMinutes = $minutes % 60;
                    $timeString = sprintf('%02d:%02d', $hours, $remainingMinutes);

                    Timesheet::create([
                        'user_id' => $user->id,
                        'task' => $task,
                        'hours' => $timeString,
                        'date' => $cursor->format('Y-m-d'),
                    ]);

                    $totalHours += $minutes / 60;
                }

                $workDaysCount++;

                // Create attendance record (simplified - just using timesheet presence)
                if (class_exists('App\Models\Attendance')) {
                    $clockIn = $cursor->copy()->setTime(8 + rand(0, 2), rand(0, 59)); // Clock in between 8-10 AM
                    $clockOut = $clockIn->copy()->addHours($totalHours)->addMinutes(rand(0, 30)); // Clock out after work hours

                    Attendance::create([
                        'user_id' => $user->id,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'date' => $cursor->format('Y-m-d'),
                    ]);
                }
            }

            $cursor->addDay();
        }

        // Add some specific data for today to ensure dashboard shows current activity
        if (!Timesheet::where('user_id', $user->id)->where('date', $today->format('Y-m-d'))->exists()) {
            $todayTasks = [
                ['task' => 'Morning Code Review', 'hours' => '1:30'],
                ['task' => 'Feature Development', 'hours' => '2:45'],
                ['task' => 'Team Standup', 'hours' => '0:30'],
                ['task' => 'Bug Investigation', 'hours' => '1:15'],
            ];

            foreach ($todayTasks as $taskData) {
                Timesheet::create([
                    'user_id' => $user->id,
                    'task' => $taskData['task'],
                    'hours' => $taskData['hours'],
                    'date' => $today->format('Y-m-d'),
                ]);
            }
        }

        // Calculate and display statistics
        $totalEntries = Timesheet::where('user_id', $user->id)->count();
        $monthlyHours = Timesheet::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $today->format('Y-m-d')])
            ->get()
            ->sum(function($entry) {
                list($h, $m) = explode(':', $entry->hours);
                return ($h * 60) + $m;
            });

        echo "Sample data created successfully!\n";
        echo "- Total timesheet entries: {$totalEntries}\n";
        echo "- Work days this month: {$workDaysCount}\n";
        echo "- Monthly hours: " . sprintf('%02d:%02d', intval($monthlyHours / 60), $monthlyHours % 60) . "\n";
        echo "- Date range: {$startOfMonth->format('M d')} - {$today->format('M d, Y')}\n";
    }
}
