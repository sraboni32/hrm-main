<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFlexibleToOfficeShiftsTable extends Migration
{
    public function up()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('office_shifts', 'is_flexible')) {
                $table->boolean('is_flexible')->default(false)->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('office_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('office_shifts', 'is_flexible')) {
                $table->dropColumn('is_flexible');
            }
        });
    }
}