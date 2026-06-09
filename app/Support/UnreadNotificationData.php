<?php

namespace App\Support;

use App\Models\Grupo;
use App\Models\User;
use App\Notifications\MensagemNaoLida;
use Illuminate\Notifications\DatabaseNotification;

class UnreadNotificationData
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function for(User $user): array
    {
        return $user->unreadNotifications()
            ->latest()
            ->get()
            ->map(fn (DatabaseNotification $notification) => [
                'id' => $notification->id,
                ...$notification->data,
                'created_at' => $notification->created_at->toISOString(),
            ])
            ->all();
    }

    public static function markGroupMessagesAsRead(User $user, Grupo $grupo): void
    {
        $user->unreadNotifications()
            ->where('type', MensagemNaoLida::class)
            ->get()
            ->filter(fn (DatabaseNotification $notification) => (int) data_get($notification->data, 'group.id') === $grupo->id)
            ->each->markAsRead();
    }
}
