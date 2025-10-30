<?php
// PdfGenerationProgress.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PdfGenerationProgress implements ShouldBroadcastNow
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
            new PrivateChannel("pdf-generation.{$this->userId}"),
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
}
