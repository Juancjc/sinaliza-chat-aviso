<?php

namespace App\Http\Controllers;

use App\Events\MensagemEnviada;
use App\Http\Requests\StoreMensagemRequest;
use App\Models\Grupo;
use App\Notifications\MensagemNaoLida;
use App\Support\UnreadNotificationData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function show(Request $request, Grupo $grupo): Response
    {
        Gate::authorize('view', $grupo);

        UnreadNotificationData::markGroupMessagesAsRead($request->user(), $grupo);

        return Inertia::render('grupos/Chat', [
            'grupo' => $grupo->load('admin:id,name')->loadCount('participantes'),
            'mensagens' => $this->mensagensDoGrupo($grupo),
        ]);
    }

    public function store(StoreMensagemRequest $request, Grupo $grupo): RedirectResponse
    {
        $mensagem = $grupo->mensagens()->create([
            'user_id' => $request->user()->id,
            'mensagem' => $request->validated('mensagem'),
        ]);

        $grupo->participantes()
            ->get()
            ->push($grupo->admin)
            ->where('id', '!=', $request->user()->id)
            ->unique('id')
            ->each->notify(new MensagemNaoLida($mensagem));

        MensagemEnviada::dispatch($mensagem);

        return back();
    }

    private function mensagensDoGrupo(Grupo $grupo)
    {
        return $grupo->mensagens()
            ->with('user:id,name,tipo_usuario,avatar_emoji')
            ->oldest()
            ->get();
    }
}
