<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'on_leave', 'inactive'])
                  ->default('active')
                  ->after('role_id');
            $table->date('leave_start_date')->nullable()->after('status');
            $table->date('leave_end_date')->nullable()->after('leave_start_date');
            $table->text('leave_reason')->nullable()->after('leave_end_date');
            
            // Add index for performance on leave queries
            $table->index(['status', 'leave_end_date'], 'users_status_leave_end_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_leave_end_date_index');
            $table->dropColumn([
                'status', 
                'leave_start_date', 
                'leave_end_date', 
                'leave_reason'
            ]);
        });
    }
};
