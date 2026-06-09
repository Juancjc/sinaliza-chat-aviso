<?php

namespace App\Notifications;

use App\Models\Aviso;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AvisoNaoLido extends Notification
{
    use Queueable;

    public function __construct(public Aviso $aviso) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->aviso->loadMissing([
            'grupo:id,nome',
            'user:id,name,avatar_emoji',
        ]);

        return [
            'kind' => 'aviso',
            'aviso_id' => $this->aviso->id,
            'group' => [
                'id' => $this->aviso->grupo->id,
                'nome' => $this->aviso->grupo->nome,
            ],
            'title' => $this->aviso->titulo,
            'message' => $this->aviso->mensagem,
            'sender' => [
                'id' => $this->aviso->user->id,
                'name' => $this->aviso->user->name,
                'avatar_emoji' => $this->aviso->user->avatar_emoji,
            ],
        ];
    }
}
