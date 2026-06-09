<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Support\UnreadNotificationData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;

class NotificacaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(UnreadNotificationData::for($request->user()));
    }

    public function read(Request $request, DatabaseNotification $notificacao): Response
    {
        abort_unless(
            $notificacao->notifiable_type === $request->user()::class
            && (int) $notificacao->notifiable_id === $request->user()->id,
            404,
        );

        $notificacao->markAsRead();

        return response()->noContent();
    }

    public function readGroupMessages(Request $request, Grupo $grupo): Response
    {
        Gate::authorize('view', $grupo);

        UnreadNotificationData::markGroupMessagesAsRead($request->user(), $grupo);

        return response()->noContent();
    }
}
