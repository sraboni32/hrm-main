<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHalfDayToOfficeShiftsTable extends Migration
{
    public function up()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('office_shifts', 'half_day_of_week')) {
                $table->tinyInteger('half_day_of_week')->nullable()->after('weekend_days');
            }
            if (!Schema::hasColumn('office_shifts', 'half_day_expected_hours')) {
                $table->decimal('half_day_expected_hours', 5, 2)->nullable()->after('half_day_of_week');
            }
        });
    }

    public function down()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('office_shifts', 'half_day_of_week')) {
                $table->dropColumn('half_day_of_week');
            }
            if (Schema::hasColumn('office_shifts', 'half_day_expected_hours')) {
                $table->dropColumn('half_day_expected_hours');
            }
        });
    }
} 