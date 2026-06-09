<?php

namespace App\Events;

use App\Models\Mensagem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensagemEnviada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Mensagem $mensagem) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $grupo = $this->mensagem->grupo;

        return $grupo->participantes()
            ->pluck('users.id')
            ->push($grupo->user_id)
            ->unique()
            ->map(fn (int $userId) => new PrivateChannel("users.{$userId}"))
            ->values()
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'mensagem.enviada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->mensagem->loadMissing([
            'grupo:id,nome',
            'user:id,name,tipo_usuario,avatar_emoji',
        ]);

        return [
            'id' => $this->mensagem->id,
            'mensagem' => $this->mensagem->mensagem,
            'created_at' => $this->mensagem->created_at->toISOString(),
            'grupo' => [
                'id' => $this->mensagem->grupo->id,
                'nome' => $this->mensagem->grupo->nome,
            ],
            'user' => [
                'id' => $this->mensagem->user->id,
                'name' => $this->mensagem->user->name,
                'tipo_usuario' => $this->mensagem->user->tipo_usuario,
                'avatar_emoji' => $this->mensagem->user->avatar_emoji,
            ],
        ];
    }
}
