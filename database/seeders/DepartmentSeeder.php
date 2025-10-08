<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the department structure
        $adminDepartment = 'Admin';
        $userDepartments = [
            'Web',
            'Graphic', 
            'Editorial',
            'Multimedia',
            'Sales',
            'Marketing',
            'Intern'
        ];

        echo "Setting up department structure...\n";

        // Get role IDs
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        if (!$adminRole || !$employeeRole) {
            echo "Error: Admin or Employee roles not found. Please run RoleSeeder first.\n";
            return;
        }

        // Update admin users
        $adminUsers = User::where('role_id', $adminRole->id)->get();
        foreach ($adminUsers as $user) {
            $user->department = $adminDepartment;
            $user->save();
            echo "Updated admin user: {$user->name} -> Department: {$adminDepartment}\n";
        }

        // Update employee users with valid departments or assign default
        $employeeUsers = User::where('role_id', $employeeRole->id)->get();
        foreach ($employeeUsers as $user) {
            // If user doesn't have a department or has an invalid one, assign a default
            if (!$user->department || !in_array($user->department, $userDepartments)) {
                $user->department = 'General'; // Default department
                $user->save();
                echo "Updated employee user: {$user->name} -> Department: General (default)\n";
            } else {
                echo "Employee user: {$user->name} -> Department: {$user->department} (valid)\n";
            }
        }

        echo "\nDepartment structure setup complete!\n";
        echo "Admin Department: {$adminDepartment}\n";
        echo "User Departments: " . implode(', ', $userDepartments) . "\n";
    }
}
