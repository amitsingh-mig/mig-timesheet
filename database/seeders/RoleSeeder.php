<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        echo "Roles created:\n";
        echo "- Admin Role ID: {$adminRole->id}\n";
        echo "- Employee Role ID: {$employeeRole->id}\n";

        // Assign roles to users
        $users = User::all();
        
        foreach ($users as $user) {
            if (!$user->role_id) {
                // Assign employee role by default (admin can be manually assigned)
                if ($user->email === 'admin@example.com') {
                    $user->role_id = $adminRole->id;
                    echo "Assigned admin role to {$user->name} ({$user->email})\n";
                } else {
                    $user->role_id = $employeeRole->id;
                    echo "Assigned employee role to {$user->name} ({$user->email})\n";
                }
                $user->save();
            } else {
                echo "User {$user->name} already has role ID: {$user->role_id}\n";
            }
        }

        echo "\nRole assignment complete!\n";
    }
}
