<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusAllowancesTable extends Migration
{
    public function up()
    {
        Schema::create('bonus_allowances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id')->index();
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['fixed', 'percentage']);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_allowances');
    }
} 