<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'metadata',
        'reply_to_id',
        'is_read',
        'read_at',
        'is_edited',
        'edited_at',
        'is_deleted'
    ];

    protected $casts = [
        'chat_room_id' => 'integer',
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'metadata' => 'array',
        'reply_to_id' => 'integer',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean'
    ];

    /**
     * Get the chat room
     */
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Get the sender
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver (for direct messages)
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the message this is replying to
     */
    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    /**
     * Get replies to this message
     */
    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'reply_to_id');
    }

    /**
     * Get attached files
     */
    public function files()
    {
        return $this->hasMany(ChatFile::class);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    /**
     * Check if message is a direct message
     */
    public function isDirectMessage()
    {
        return !is_null($this->receiver_id);
    }

    /**
     * Check if message has files
     */
    public function hasFiles()
    {
        return $this->files()->exists();
    }

    /**
     * Get formatted message for display
     */
    public function getFormattedMessageAttribute()
    {
        if ($this->type === 'file') {
            return $this->files->first() ? 
                'Shared a file: ' . $this->files->first()->original_name : 
                'Shared a file';
        }
        
        if ($this->type === 'image') {
            return $this->files->first() ? 
                'Shared an image: ' . $this->files->first()->original_name : 
                'Shared an image';
        }

        return $this->message;
    }

    /**
     * Scope for direct messages between two users
     */
    public function scopeDirectMessagesBetween($query, $user1Id, $user2Id)
    {
        return $query->where(function ($q) use ($user1Id, $user2Id) {
            $q->where(function ($subQ) use ($user1Id, $user2Id) {
                $subQ->where('sender_id', $user1Id)
                     ->where('receiver_id', $user2Id);
            })->orWhere(function ($subQ) use ($user1Id, $user2Id) {
                $subQ->where('sender_id', $user2Id)
                     ->where('receiver_id', $user1Id);
            });
        })->whereNull('chat_room_id');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
