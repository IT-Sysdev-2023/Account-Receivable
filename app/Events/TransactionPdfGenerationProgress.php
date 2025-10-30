<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TransactionPdfGenerationProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $userId,
        public int $progress,
        public string $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("transaction-pdf-generation.{$this->userId}"),
        ];
    }

    public function broadcastWith()
    {
        return [
            'progress' => $this->progress,
            'message' => $this->message,
            'userId' => $this->userId
        ];
    }

    public function broadcastAs()
    {
        Log::info('The TransactionPdfGenerationProgress broadcasted.');
        return 'TransactionPdfGenerationProgress';
    }
}
