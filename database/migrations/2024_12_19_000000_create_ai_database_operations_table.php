<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiDatabaseOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_database_operations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned(); // Match users table id type
            $table->string('user_role', 50);
            $table->text('original_question');
            $table->text('generated_sql');
            $table->string('operation_type', 20); // select, insert, update, delete
            $table->json('query_analysis')->nullable();
            $table->json('result_summary')->nullable();
            $table->integer('affected_rows')->nullable();
            $table->integer('result_count')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->decimal('execution_time', 8, 3)->nullable(); // in seconds
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('user_role');
            $table->index('operation_type');
            $table->index('success');
            $table->index('created_at');

            // Foreign key - commented out for now due to type mismatch
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_database_operations');
    }
}
