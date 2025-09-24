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
    
    // If no users exist, create a test user
    if ($userCount == 0) {
        echo "\nNo users found. Creating test user...\n";
        
        // First create admin role if it doesn't exist
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
        
        // Create test user
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✓ Created test user with ID: $userId\n";
        echo "✓ Login credentials: admin@test.com / password123\n";
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
