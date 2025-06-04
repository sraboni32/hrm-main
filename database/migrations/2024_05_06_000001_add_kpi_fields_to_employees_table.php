<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKpiFieldsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'mode')) {
                $table->string('mode', 32)->nullable()->after('employment_type');
            }
            if (!Schema::hasColumn('employees', 'expected_hours')) {
                $table->decimal('expected_hours', 5, 2)->nullable()->after('mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'mode')) {
                $table->dropColumn('mode');
            }
            if (Schema::hasColumn('employees', 'expected_hours')) {
                $table->dropColumn('expected_hours');
            }
        });
    }
} 