<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUpdatedStatusToSalaryDisbursements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'updated' status to the enum
        DB::statement("ALTER TABLE salary_disbursements MODIFY COLUMN status ENUM('generated', 'sent_for_review', 'reviewed', 'feedback', 'updated', 'approved', 'paid') NOT NULL DEFAULT 'generated'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove 'updated' status from the enum
        DB::statement("ALTER TABLE salary_disbursements MODIFY COLUMN status ENUM('generated', 'sent_for_review', 'reviewed', 'feedback', 'approved', 'paid') NOT NULL DEFAULT 'generated'");
    }
}
