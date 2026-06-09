<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMensagemRequest;
use App\Models\Grupo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function show(Grupo $grupo): Response
    {
        Gate::authorize('view', $grupo);

        return Inertia::render('grupos/Chat', [
            'grupo' => $grupo->load('admin:id,name')->loadCount('participantes'),
            'mensagens' => $this->mensagensDoGrupo($grupo),
        ]);
    }

    public function messages(Grupo $grupo): JsonResponse
    {
        Gate::authorize('view', $grupo);

        return response()->json($this->mensagensDoGrupo($grupo));
    }

    public function store(StoreMensagemRequest $request, Grupo $grupo): RedirectResponse
    {
        $grupo->mensagens()->create([
            'user_id' => $request->user()->id,
            'mensagem' => $request->validated('mensagem'),
        ]);

        return back();
    }

    private function mensagensDoGrupo(Grupo $grupo)
    {
        return $grupo->mensagens()
            ->with('user:id,name,tipo_usuario')
            ->oldest()
            ->get();
    }
}
