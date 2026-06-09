<?php

namespace App\Notifications;

use App\Models\Mensagem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MensagemNaoLida extends Notification
{
    use Queueable;

    public function __construct(public Mensagem $mensagem) {}

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
        $this->mensagem->loadMissing([
            'grupo:id,nome,user_id',
            'user:id,name,avatar_emoji',
        ]);

        return [
            'kind' => 'mensagem',
            'message_id' => $this->mensagem->id,
            'group' => [
                'id' => $this->mensagem->grupo->id,
                'nome' => $this->mensagem->grupo->nome,
            ],
            'title' => $this->mensagem->user->name,
            'message' => $this->mensagem->mensagem,
            'sender' => [
                'id' => $this->mensagem->user->id,
                'name' => $this->mensagem->user->name,
                'avatar_emoji' => $this->mensagem->user->avatar_emoji,
            ],
        ];
    }
}
