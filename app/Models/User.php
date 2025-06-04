<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable , HasRoles;
    protected $dates = ['deleted_at'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'status', 'avatar','role_users_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_users_id' => 'integer',
        'status' => 'integer',
    ];


    public function RoleUser()
	{
        return $this->hasone('Spatie\Permission\Models\Role','id',"role_users_id");
	}

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class, 'email', 'email');
    }

    public function aiChatConversations()
    {
        return $this->hasMany(\App\Models\AiChatConversation::class);
    }

    // Chat relationships
    public function chatRoomMemberships()
    {
        return $this->hasMany(\App\Models\ChatRoomMember::class);
    }

    public function chatRooms()
    {
        return $this->belongsToMany(\App\Models\ChatRoom::class, 'chat_room_members');
    }

    public function sentMessages()
    {
        return $this->hasMany(\App\Models\ChatMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(\App\Models\ChatMessage::class, 'receiver_id');
    }

    public function createdChatRooms()
    {
        return $this->hasMany(\App\Models\ChatRoom::class, 'created_by');
    }
}
