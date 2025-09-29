<?php
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Test database connection
    echo "Testing database connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
    
    // Check users table
    $userCount = DB::table('users')->count();
    echo "✓ Users in database: $userCount\n";
    
    // Check roles table
    $roleCount = DB::table('roles')->count(); 
    echo "✓ Roles in database: $roleCount\n";
    
    // Check attendances table
    $attendanceCount = DB::table('attendances')->count();
    echo "✓ Attendance records: $attendanceCount\n";
    
    // If no users exist, create the provided admin user
    if ($userCount == 0) {
        echo "\nNo users found. Creating initial admin user...\n";

        // Ensure roles exist
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRoleId = DB::table('roles')->insertGetId([
                'name' => 'admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✓ Created admin role with ID: $adminRoleId\n";
        } else {
            $adminRoleId = $adminRole->id;
            echo "✓ Admin role already exists with ID: $adminRoleId\n";
        }

        $employeeRole = DB::table('roles')->where('name', 'employee')->first();
        if (!$employeeRole) {
            $employeeRoleId = DB::table('roles')->insertGetId([
                'name' => 'employee',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✓ Created employee role with ID: $employeeRoleId\n";
        } else {
            echo "✓ Employee role already exists with ID: {$employeeRole->id}\n";
        }

        // Create admin@example.com user
        $adminUserId = DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Admin@9711#31$'),
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "✓ Created admin user with ID: $adminUserId\n";
        echo "✓ Login credentials: admin@example.com / Admin@9711#31$\n";
    }

    // Ensure the specified admin user exists and is up to date
    $adminRole = DB::table('roles')->where('name', 'admin')->first();
    if ($adminRole) {
        $existingAdmin = DB::table('users')->where('email', 'admin@example.com')->first();
        if ($existingAdmin) {
            DB::table('users')->where('id', $existingAdmin->id)->update([
                'name' => 'Admin',
                'password' => Hash::make('Admin@9711#31$'),
                'role_id' => $adminRole->id,
                'updated_at' => now(),
                'email_verified_at' => $existingAdmin->email_verified_at ?: now(),
            ]);
            echo "✓ Updated existing admin user (admin@example.com)\n";
        } else {
            $newAdminId = DB::table('users')->insertGetId([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin@9711#31$'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✓ Created admin user with ID: $newAdminId\n";
        }
    }

    // Optionally ensure the specified employee user exists (creates with a temporary password if missing)
    $employeeRole = DB::table('roles')->where('name', 'employee')->first();
    if ($employeeRole) {
        $employeeEmail = 'amitrajput30205@gmail.com';
        $existingEmployee = DB::table('users')->where('email', $employeeEmail)->first();
        if (!$existingEmployee) {
            $tmpPassword = 'User@12345!';
            $empId = DB::table('users')->insertGetId([
                'name' => 'Amit Rajput',
                'email' => $employeeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make($tmpPassword),
                'role_id' => $employeeRole->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✓ Created employee user with ID: $empId (temporary password: $tmpPassword)\n";
        } else {
            echo "✓ Employee user already exists: $employeeEmail\n";
        }
    }
    
    echo "\n=== Connection Test Results ===\n";
    echo "Database: Connected ✓\n";
    echo "Tables: Available ✓\n";
    echo "Users: $userCount\n";
    echo "Application should be working properly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
