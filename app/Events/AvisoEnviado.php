<?php

namespace App\Events;

use App\Models\Aviso;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvisoEnviado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, int>  $userIds
     */
    public function __construct(public Aviso $aviso, public array $userIds) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return collect($this->userIds)
            ->unique()
            ->map(fn (int $userId) => new PrivateChannel("users.{$userId}"))
            ->values()
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'aviso.enviado';
    }

    /**
     * @return array<string, int>
     */
    public function broadcastWith(): array
    {
        return ['aviso_id' => $this->aviso->id];
    }
}
