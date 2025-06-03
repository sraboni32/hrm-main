<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeekendDaysToOfficeShiftsTable extends Migration
{
    public function up()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('office_shifts', 'weekend_days')) {
                $table->string('weekend_days')->nullable()->after('expected_hours');
            }
        });
    }

    public function down()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('office_shifts', 'weekend_days')) {
                $table->dropColumn('weekend_days');
            }
        });
    }
} 