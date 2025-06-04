<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_room_id')->nullable();
            $table->unsignedInteger('sender_id');
            $table->unsignedInteger('receiver_id')->nullable(); // For direct messages
            $table->text('message')->nullable();
            $table->enum('type', ['text', 'file', 'image', 'system', 'emoji'])->default('text');
            $table->json('metadata')->nullable(); // For file info, reply info, etc.
            $table->unsignedBigInteger('reply_to_id')->nullable(); // For threaded conversations
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['chat_room_id', 'created_at']);
            $table->index(['sender_id', 'receiver_id', 'created_at']);
            $table->index(['receiver_id', 'is_read']);
            $table->index('reply_to_id');
            $table->index('sender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}
