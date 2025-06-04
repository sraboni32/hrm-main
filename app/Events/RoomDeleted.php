<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $roomName;
    public $deletedBy;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($roomId, $roomName, $deletedBy)
    {
        $this->roomId = $roomId;
        $this->roomName = $roomName;
        $this->deletedBy = $deletedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast to all users who were in the room
        return new PrivateChannel('chat-room.' . $this->roomId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'room.deleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'room_id' => $this->roomId,
            'room_name' => $this->roomName,
            'deleted_by' => $this->deletedBy,
            'timestamp' => now()->toISOString()
        ];
    }
}
