<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'session_id', 'title', 'is_active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AiChatMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(AiChatMessage::class, 'conversation_id')->latest();
    }

    /**
     * Get conversation context for AI
     */
    public function getContextMessages($limit = 10)
    {
        return $this->messages()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($message) {
                return [
                    'role' => $message->type === 'user' ? 'user' : 'assistant',
                    'content' => $message->message
                ];
            })
            ->toArray();
    }
}
