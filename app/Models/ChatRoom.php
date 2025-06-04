<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ChatRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'created_by',
        'settings',
        'is_active',
        'last_activity'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_activity' => 'datetime',
        'created_by' => 'integer'
    ];

    /**
     * Get the user who created the chat room
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all members of the chat room
     */
    public function members()
    {
        return $this->hasMany(ChatRoomMember::class);
    }

    /**
     * Get active members of the chat room
     */
    public function activeMembers()
    {
        return $this->hasMany(ChatRoomMember::class)->where('is_banned', false);
    }

    /**
     * Get all messages in the chat room
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->where('is_deleted', false);
    }

    /**
     * Get recent messages
     */
    public function recentMessages($limit = 50)
    {
        return $this->messages()
            ->with(['sender', 'files'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Check if user is member of this room
     */
    public function hasMember($userId)
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Add member to chat room
     */
    public function addMember($userId, $role = 'member')
    {
        return $this->members()->firstOrCreate(
            ['user_id' => $userId],
            ['role' => $role, 'joined_at' => now()]
        );
    }

    /**
     * Remove member from chat room
     */
    public function removeMember($userId)
    {
        return $this->members()->where('user_id', $userId)->delete();
    }

    /**
     * Update last activity
     */
    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Get unread message count for user
     */
    public function getUnreadCountForUser($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Check if user can delete this room
     */
    public function canBeDeletedBy($userId)
    {
        $member = $this->members()->where('user_id', $userId)->first();
        $user = \App\Models\User::find($userId);

        // Super admin can delete any room
        if ($user && $user->role_users_id == 1) {
            return true;
        }

        // Room admin can delete the room
        return $member && $member->role === 'admin';
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a room, also delete related data manually
        static::deleting(function ($room) {
            // Get all messages with files
            $messages = $room->messages()->with('files')->get();

            foreach ($messages as $message) {
                foreach ($message->files as $file) {
                    // Delete file from storage
                    if (Storage::exists($file->file_path)) {
                        Storage::delete($file->file_path);
                    }
                    // Delete file record
                    $file->delete();
                }
                // Delete message
                $message->delete();
            }

            // Delete all room members
            $room->members()->delete();
        });
    }
}
