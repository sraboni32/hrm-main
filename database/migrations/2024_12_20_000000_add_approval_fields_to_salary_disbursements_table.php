<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddApprovalFieldsToSalaryDisbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_disbursements', function (Blueprint $table) {
            // Add approval workflow fields
            $table->unsignedInteger('approved_by')->nullable()->after('reviewed_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedInteger('paid_by')->nullable()->after('approved_at');
            $table->timestamp('paid_at')->nullable()->after('paid_by');
        });

        // Update the enum separately using raw SQL to avoid Doctrine issues
        DB::statement("ALTER TABLE salary_disbursements MODIFY COLUMN status ENUM('generated', 'sent_for_review', 'reviewed', 'feedback', 'approved', 'paid') NOT NULL DEFAULT 'generated'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_disbursements', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_at', 'paid_by', 'paid_at']);
        });

        // Revert status enum to original values using raw SQL
        DB::statement("ALTER TABLE salary_disbursements MODIFY COLUMN status ENUM('generated', 'sent_for_review', 'reviewed', 'updated', 'paid') NOT NULL DEFAULT 'generated'");
    }
}
