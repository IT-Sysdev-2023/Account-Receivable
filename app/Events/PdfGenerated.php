<?php
// PdfGenerated.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PdfGenerated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $userId,
        public string $filename,
        public string $path,
        public string $channel,
        public ?array $excelData = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("pdf-generation.{$this->userId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'userId' => $this->userId,
            'filename' => $this->filename,
            'path' => $this->path,
            'channel' => $this->channel,
            'excelData' => $this->excelData,
        ];
    }
}
