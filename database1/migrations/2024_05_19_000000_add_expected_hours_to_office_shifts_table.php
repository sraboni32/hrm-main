<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpectedHoursToOfficeShiftsTable extends Migration
{
    public function up()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('office_shifts', 'expected_hours')) {
                $table->decimal('expected_hours', 5, 2)->nullable()->after('is_flexible');
            }
        });
    }

    public function down()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('office_shifts', 'expected_hours')) {
                $table->dropColumn('expected_hours');
            }
        });
    }
} 