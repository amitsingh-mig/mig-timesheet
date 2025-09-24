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
        Schema::create('timesheet_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('max_daily_hours', 4, 2)->default(12.00);
            $table->decimal('max_weekly_hours', 4, 2)->default(40.00);
            $table->boolean('require_approval')->default(true);
            $table->decimal('overtime_threshold', 4, 2)->default(8.00);
            $table->boolean('allow_retroactive_entries')->default(true);
            $table->integer('retroactive_limit_days')->default(7);
            $table->timestamps();
        });
        
        // Create user-policies pivot table
        Schema::create('user_policies', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('policy_id');
            $table->timestamps();
            
            $table->primary(['user_id', 'policy_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('policy_id')->references('id')->on('timesheet_policies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_policies');
        Schema::dropIfExists('timesheet_policies');
    }
};
