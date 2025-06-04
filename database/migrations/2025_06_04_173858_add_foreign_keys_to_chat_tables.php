<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToChatTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add foreign key constraints to chat_room_members table
        Schema::table('chat_room_members', function (Blueprint $table) {
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to chat_messages table
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('chat_messages')->onDelete('set null');
        });

        // Add foreign key constraints to chat_files table
        Schema::table('chat_files', function (Blueprint $table) {
            $table->foreign('chat_message_id')->references('id')->on('chat_messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove foreign key constraints
        Schema::table('chat_files', function (Blueprint $table) {
            $table->dropForeign(['chat_message_id']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['chat_room_id']);
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropForeign(['reply_to_id']);
        });

        Schema::table('chat_room_members', function (Blueprint $table) {
            $table->dropForeign(['chat_room_id']);
            $table->dropForeign(['user_id']);
        });
    }
}
