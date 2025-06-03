<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminResponseFieldsToSalaryDisbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_disbursements', function (Blueprint $table) {
            // Add admin response fields
            $table->text('admin_response')->nullable()->after('feedback');
            $table->unsignedInteger('admin_response_by')->nullable()->after('admin_response');
            $table->timestamp('admin_response_at')->nullable()->after('admin_response_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_disbursements', function (Blueprint $table) {
            $table->dropColumn(['admin_response', 'admin_response_by', 'admin_response_at']);
        });
    }
}
