<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Role::updateOrCreate(
            ['id' => 1],
            ['name' => 'admin']);

        Role::updateOrCreate(
            ['id' => 2],
            ['name' => 'employee']);

        // Create default admin and employee accounts
        $adminRole = Role::where('name','admin')->first();
        $employeeRole = Role::where('name','employee')->first();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin@9711#31$'),
                'role_id' => $adminRole?->id ?? 1,
                'department' => 'Admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Employee One',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole?->id ?? 2,
                'department' => 'General',
            ]
        );

        // Run department seeder to ensure proper department assignments
        $this->call([
            DepartmentSeeder::class,
        ]);
    }
}
