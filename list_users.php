<?php
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Available Users for Login ===\n\n";
    
    $users = DB::table('users')
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
        ->select('users.id', 'users.name', 'users.email', 'roles.name as role_name')
        ->get();
    
    if ($users->count() == 0) {
        echo "❌ No users found in database!\n";
        echo "Run the debug_connection.php script to create a test user.\n";
    } else {
        foreach ($users as $user) {
            echo "👤 User ID: {$user->id}\n";
            echo "   Name: {$user->name}\n";
            echo "   Email: {$user->email}\n";
            echo "   Role: " . ($user->role_name ?? 'No Role') . "\n";
            echo "   ---\n";
        }
        
        echo "\n💡 To access the dashboard:\n";
        echo "1. Go to: http://localhost:8000/login\n";
        echo "2. Log in with one of the above email addresses\n";
        echo "3. If you don't know the password, you can reset it or create a new test user\n";
        echo "\n🔧 To create a test user with known credentials:\n";
        echo "Run: php debug_connection.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
