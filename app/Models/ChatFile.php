<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChatFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_message_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'file_hash',
        'metadata',
        'is_public'
    ];

    protected $casts = [
        'chat_message_id' => 'integer',
        'file_size' => 'integer',
        'metadata' => 'array',
        'is_public' => 'boolean'
    ];

    /**
     * Get the chat message
     */
    public function chatMessage()
    {
        return $this->belongsTo(ChatMessage::class);
    }

    /**
     * Get file URL
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Check if file is a video
     */
    public function isVideo()
    {
        return strpos($this->mime_type, 'video/') === 0;
    }

    /**
     * Check if file is an audio
     */
    public function isAudio()
    {
        return strpos($this->mime_type, 'audio/') === 0;
    }

    /**
     * Check if file is a document
     */
    public function isDocument()
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain'
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Get file icon based on type
     */
    public function getIconAttribute()
    {
        if ($this->isImage()) {
            return 'fas fa-image';
        } elseif ($this->isVideo()) {
            return 'fas fa-video';
        } elseif ($this->isAudio()) {
            return 'fas fa-music';
        } elseif ($this->isDocument()) {
            return 'fas fa-file-alt';
        } else {
            return 'fas fa-file';
        }
    }

    /**
     * Delete file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }
        });
    }
}
