<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('conversation_id')->index('ai_chat_messages_conversation_id');
            $table->enum('type', ['user', 'assistant']);
            $table->text('message');
            $table->json('metadata')->nullable(); // Store additional context, tokens used, etc.
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('ai_chat_conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_chat_messages');
    }
}
