<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $roomId;
    public $receiverId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $roomId = null, $receiverId = null)
    {
        $this->user = $user;
        $this->roomId = $roomId;
        $this->receiverId = $receiverId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if ($this->roomId) {
            // Room typing
            return new PrivateChannel('chat-room.' . $this->roomId);
        } else {
            // Direct message typing
            return new PrivateChannel('user.' . $this->receiverId);
        }
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'user.typing';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username
            ],
            'room_id' => $this->roomId,
            'receiver_id' => $this->receiverId,
            'timestamp' => now()->toISOString()
        ];
    }
}
