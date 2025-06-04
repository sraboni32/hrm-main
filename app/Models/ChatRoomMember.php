<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoomMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'role',
        'joined_at',
        'last_seen',
        'is_muted',
        'is_banned'
    ];

    protected $casts = [
        'chat_room_id' => 'integer',
        'user_id' => 'integer',
        'joined_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_muted' => 'boolean',
        'is_banned' => 'boolean'
    ];

    /**
     * Get the chat room
     */
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update last seen timestamp
     */
    public function updateLastSeen()
    {
        $this->update(['last_seen' => now()]);
    }

    /**
     * Check if user is online (seen within last 5 minutes)
     */
    public function isOnline()
    {
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) <= 5;
    }

    /**
     * Check if user is admin or moderator
     */
    public function canModerate()
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    /**
     * Check if user can send messages
     */
    public function canSendMessages()
    {
        return !$this->is_muted && !$this->is_banned;
    }
}
