<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipanteRequest;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GrupoParticipanteController extends Controller
{
    public function index(Grupo $grupo): Response
    {
        Gate::authorize('manageParticipants', $grupo);

        $conviteAtivo = $grupo->convites()->valid()->latest()->first();

        return Inertia::render('grupos/Participantes', [
            'grupo' => $grupo->only('id', 'nome', 'descricao'),
            'conviteAtivo' => $conviteAtivo ? [
                'token' => $conviteAtivo->token,
                'url' => route('grupos.convites.show', $conviteAtivo, absolute: false),
                'expires_at' => $conviteAtivo->expires_at,
            ] : null,
            'participantes' => $grupo->participantes()
                ->select('users.id', 'users.name', 'users.email', 'users.avatar_emoji')
                ->orderBy('users.name')
                ->get(),
            'alunosDisponiveis' => User::query()
                ->where('tipo_usuario', 'aluno')
                ->whereDoesntHave('gruposParticipando', fn ($query) => $query->whereKey($grupo->id))
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreParticipanteRequest $request, Grupo $grupo): RedirectResponse
    {
        $grupo->participantes()->attach($request->validated('user_id'), ['status' => 'ativo']);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aluno adicionado ao grupo.']);

        return back();
    }

    public function destroy(Grupo $grupo, User $participante): RedirectResponse
    {
        Gate::authorize('manageParticipants', $grupo);
        abort_unless(
            $participante->tipo_usuario === 'aluno'
            && $grupo->participantes()->whereKey($participante->id)->exists(),
            404,
        );

        $grupo->participantes()->detach($participante->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aluno removido do grupo.']);

        return back();
    }
}
