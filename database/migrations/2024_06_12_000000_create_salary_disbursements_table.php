<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryDisbursementsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_disbursements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->string('month', 7); // e.g., '2024-06'
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('adjustments', 10, 2)->default(0);
            $table->decimal('leave_deductions', 10, 2)->default(0);
            $table->decimal('bonus_allowance', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_payable', 10, 2);
            $table->boolean('paid')->default(false);
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('sent_for_review_at')->nullable();
            $table->timestamp('feedback_at')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->index(['employee_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_disbursements');
    }
}