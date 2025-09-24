<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('description');
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->boolean('is_overtime')->default(false)->after('rejection_reason');
            $table->decimal('expected_hours', 4, 2)->default(8.00)->after('is_overtime');
            $table->decimal('break_duration', 4, 2)->default(0.00)->after('expected_hours');
            $table->string('location')->nullable()->after('break_duration');
            $table->unsignedBigInteger('project_id')->nullable()->after('location');
            $table->text('notes')->nullable()->after('project_id');
            $table->timestamp('submitted_at')->nullable()->after('notes');
            $table->unsignedBigInteger('last_modified_by')->nullable()->after('submitted_at');
            
            // Foreign key constraints
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('last_modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['last_modified_by']);
            
            $table->dropColumn([
                'status',
                'approved_by',
                'approved_at',
                'rejection_reason',
                'is_overtime',
                'expected_hours',
                'break_duration',
                'location',
                'project_id',
                'notes',
                'submitted_at',
                'last_modified_by'
            ]);
        });
    }
};
