<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PublicDisplayUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $tenantSlug,
        public string $turnCode,
        public string $desk,
        public ?string $message = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel($this->channelName()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PublicDisplayUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'tenant_slug' => $this->tenantSlug,
            'turn_code' => $this->turnCode,
            'desk' => $this->desk,
            'message' => $this->message,
            'channel' => $this->channelName(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function channelName(): string
    {
        return sprintf('public-display.%s', $this->tenantSlug);
    }
}
