<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'type', 'message', 'metadata'
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'metadata' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiChatConversation::class, 'conversation_id');
    }

    /**
     * Check if message is from user
     */
    public function isFromUser()
    {
        return $this->type === 'user';
    }

    /**
     * Check if message is from assistant
     */
    public function isFromAssistant()
    {
        return $this->type === 'assistant';
    }
}
