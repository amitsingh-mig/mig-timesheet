<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->time('hours')->change();
        });
    }

    public function down()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->decimal('hours', 5, 2)->change();
        });
    }
};


