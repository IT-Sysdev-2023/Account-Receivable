<?php

namespace App\Events;

use App\Models\MasterfileModels\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reader;
    public $senderId;
    public $conversationUserId;

    /**
     * Create a new event instance.
     */
    public function __construct(User $reader, $senderId)
    {
        $this->reader = $reader;
        $this->senderId = $senderId;
        $this->conversationUserId = $reader->id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->senderId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'reader' => [
                'id' => $this->reader->id,
                'name' => $this->reader->name,
            ],
            'conversation_user_id' => $this->conversationUserId,
            'read_at' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MessageRead';
    }
}
