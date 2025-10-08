<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

class SetupDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:departments {--force : Force update even if departments are already set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup department structure for users based on their roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up department structure...');

        // Get role IDs
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        if (!$adminRole || !$employeeRole) {
            $this->error('Admin or Employee roles not found. Please run RoleSeeder first.');
            return 1;
        }

        $force = $this->option('force');
        $updatedCount = 0;

        // Update admin users
        $adminUsers = User::where('role_id', $adminRole->id)->get();
        foreach ($adminUsers as $user) {
            if ($force || !$user->department || $user->department !== 'Admin') {
                $user->department = 'Admin';
                $user->save();
                $this->line("Updated admin user: {$user->name} -> Department: Admin");
                $updatedCount++;
            }
        }

        // Update employee users
        $employeeUsers = User::where('role_id', $employeeRole->id)->get();
        $validDepartments = ['Web', 'Graphic', 'Editorial', 'Multimedia', 'Sales', 'Marketing', 'Intern', 'General'];

        foreach ($employeeUsers as $user) {
            if ($force || !$user->department || !in_array($user->department, $validDepartments)) {
                $user->department = 'General'; // Default department
                $user->save();
                $this->line("Updated employee user: {$user->name} -> Department: General (default)");
                $updatedCount++;
            }
        }

        $this->info("Department setup complete! Updated {$updatedCount} users.");
        
        // Display summary
        $this->table(
            ['Role', 'Department', 'Count'],
            [
                ['Admin', 'Admin', User::where('department', 'Admin')->count()],
                ['Employee', 'Web', User::where('department', 'Web')->count()],
                ['Employee', 'Graphic', User::where('department', 'Graphic')->count()],
                ['Employee', 'Editorial', User::where('department', 'Editorial')->count()],
                ['Employee', 'Multimedia', User::where('department', 'Multimedia')->count()],
                ['Employee', 'Sales', User::where('department', 'Sales')->count()],
                ['Employee', 'Marketing', User::where('department', 'Marketing')->count()],
                ['Employee', 'Intern', User::where('department', 'Intern')->count()],
                ['Employee', 'General', User::where('department', 'General')->count()],
            ]
        );

        return 0;
    }
}