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
        // Update existing users to have proper department assignments
        DB::table('users')->where('role_id', function($query) {
            $query->select('id')
                  ->from('roles')
                  ->where('name', 'admin');
        })->update(['department' => 'Admin']);

        // Update existing employee users to have default department if null
        DB::table('users')->where('role_id', function($query) {
            $query->select('id')
                  ->from('roles')
                  ->where('name', 'employee');
        })->whereNull('department')->update(['department' => 'General']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset department assignments
        DB::table('users')->update(['department' => null]);
    }
};
